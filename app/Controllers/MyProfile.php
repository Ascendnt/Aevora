<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use App\Models\EmployeeProfileChangeRequestModel;
use App\Models\NotificationModel;

/**
 * An employee's own profile: read-only for everything HR manages
 * (department, position, pay, supervisor, etc.), with a request-based edit
 * flow for a handful of personal contact fields. Requests sit pending until
 * HR/superadmin approves or rejects them from Employee Management.
 */
class MyProfile extends BaseController
{
    /** Fields an employee may propose changes to themselves. */
    private const EDITABLE_FIELDS = ['date_of_birth', 'phone', 'address', 'emergency_contact_name', 'emergency_contact_phone'];

    private const FIELD_LABELS = [
        'date_of_birth'           => 'Date of birth',
        'phone'                   => 'Phone number',
        'address'                 => 'Address',
        'emergency_contact_name'  => 'Emergency contact name',
        'emergency_contact_phone' => 'Emergency contact phone',
    ];

    public function index()
    {
        $employee = current_employee();

        if (! $employee) {
            return redirect()->to('/dashboard')->with('error', 'Superadmin accounts have no employee profile of their own.');
        }

        $details = (new EmployeeModel())->findWithDetails((int) $employee['id']);
        $pending = (new EmployeeProfileChangeRequestModel())->pendingForEmployee((int) $employee['id']);

        return view('my_profile/index', [
            'title'         => 'My profile',
            'active'        => 'my-profile',
            'employee'      => $details,
            'pending'       => $pending,
            'pendingChanges' => $pending ? json_decode((string) $pending['requested_changes'], true) : [],
            'history'       => (new EmployeeProfileChangeRequestModel())->historyForEmployee((int) $employee['id']),
            'fieldLabels'   => self::FIELD_LABELS,
        ]);
    }

    public function requestEdit()
    {
        $employee = current_employee();
        if (! $employee) {
            return redirect()->to('/dashboard')->with('error', 'Superadmin accounts have no employee profile of their own.');
        }

        $employeeId = (int) $employee['id'];
        $requests   = new EmployeeProfileChangeRequestModel();

        if ($requests->pendingForEmployee($employeeId)) {
            return redirect()->to('/my-profile')->with('error', 'You already have a pending request — wait for it to be reviewed before submitting another.');
        }

        $current = (new EmployeeModel())->find($employeeId);
        $post    = $this->request->getPost();
        $changes = [];

        foreach (self::EDITABLE_FIELDS as $field) {
            $newValue = trim((string) ($post[$field] ?? ''));
            $oldValue = (string) ($current[$field] ?? '');

            if ($newValue !== $oldValue) {
                $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        if ($changes === []) {
            return redirect()->to('/my-profile')->with('error', 'No changes were made.');
        }

        $requests->insert([
            'employee_id'       => $employeeId,
            'requested_changes' => json_encode($changes),
            'employee_note'     => trim((string) ($post['employee_note'] ?? '')) ?: null,
            'status'            => 'pending',
        ]);

        $this->notifySupervisor($employee, count($changes));

        return redirect()->to('/my-profile')->with('success', 'Your change request was submitted for HR review.');
    }

    /** Best-effort heads-up to the employee's supervisor — the HR profile-requests list is the reliable path regardless. */
    private function notifySupervisor(array $employee, int $changeCount): void
    {
        if (empty($employee['supervisor_id'])) {
            return;
        }

        $supervisor = (new EmployeeModel())->find((int) $employee['supervisor_id']);
        if (! $supervisor) {
            return;
        }

        $requesterName = (string) (session()->get('user_name') ?? 'An employee');

        (new NotificationModel())->insert([
            'user_id' => (int) $supervisor['user_id'],
            'message' => "{$requesterName} requested {$changeCount} profile change(s) for HR review.",
            'type'    => 'profile_change_request',
            'link'    => '/employee-management/profile-requests',
            'is_read' => false,
        ]);
    }
}
