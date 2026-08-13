<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use App\Models\ProjectDeveloperModel;
use App\Models\UserModel;

class Projects extends BaseController
{
    protected $projectModel;
    protected $projectDevModel;
    protected $userModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->projectDevModel = new ProjectDeveloperModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $statusFilter = $this->request->getGet('status');
        $keyword      = $this->request->getGet('keyword');

        $data = [
            'title'          => 'Project Tracker',
            'projects'       => $this->projectModel->getProjectsWithDevelopers($statusFilter, $keyword),
            'user'           => $this->userModel->where('is_active', 1)->findAll(),
            'selectedStatus' => $statusFilter,
            'keyword'        => $keyword,
            'statusOptions'  => ['Planning', 'In Progress', 'Testing/QA', 'Review', 'Completed', 'On Hold']
        ];

        return view('projects/index', $data);
    }

    public function detail($id)
    {
        $project = $this->projectModel->getProjectDetail($id);

        if (!$project) {
            return redirect()->to('/projects')->with('error', 'Project tidak ditemukan.');
        }

        $data = [
            'title'    => 'Detail Project - ' . $project['name'],
            'project'  => $project,
            'users'    => $this->userModel->where('is_active', 1)->findAll(),
        ];

        return view('projects/detail', $data);
    }

    public function store()
    {
        $rules = [
            'project_code' => 'required',
            'name'        => 'required|max_length[250]',
            'status'      => 'required',
            'start_date'  => 'required|valid_date',
            'end_date'    => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $projectData = [
            'project_code'      => $this->request->getPost('project_code'),
            'name'              => $this->request->getPost('name'),
            'notes'             => $this->request->getPost('notes'),
            'status'            => $this->request->getPost('status'),
            'progress'          => $this->request->getPost('progress') ?? 0,
            'start_date'        => $this->request->getPost('start_date'),
            'end_date'          => $this->request->getPost('end_date'),
            'unit_testing_date' => $this->request->getPost('unit_testing_date') ?: null,
            'sit_date'          => $this->request->getPost('sit_date') ?: null,
            'uat_date'          => $this->request->getPost('uat_date') ?: null,
            'promote_date'      => $this->request->getPost('promote_date') ?: null,
        ];

        $projectId = $this->projectModel->insert($projectData);

        //assign developer PIC
        $developerIds = $this->request->getPost('developer_ids');
        if (!empty($developerIds) && is_array($developerIds)) {
            foreach ($developerIds as $userId) {
                $this->projectDevModel->insert([
                    'project_id' => $projectId,
                    'user_id'    => $userId
                ]);
            }
        }

        return redirect()->to('/projects')->with('success', 'Project berhasil ditambahkan!');
    }

    public function updateProgress($id)
    {
        $progress = (int)$this->request->getPost('progress');
        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');

        $this->projectModel->update($id, [
            'progress' => $progress,
            'status' => $status,
            'notes' => $notes
        ]);

        return redirect()->back()->with('success', 'Progress project berhasil diperbarui!');
    }

    public function delete($id)
    {
        $this->projectDevModel->where('project_id', $id)->delete();
        $this->projectModel->delete($id);

        return redirect()->to('/projects')->with('success', 'Project berhasil dihapus!');
    }
}
