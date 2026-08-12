<?php

namespace App\Controllers;

class Daily_project_updates extends Security_Controller
{
    public $Daily_project_updates_model;
    public $Projects_model;
    public $Timesheets_model;

    public function __construct()
    {
        parent::__construct();
        $this->access_only_team_members();
        $this->Timesheets_model = model('App\Models\Timesheets_model');
    }

    public function get_updates()
    {
        $date = $this->request->getPost('date');
        if (!$date) {
            $date = date('Y-m-d');
        } else {
            $date = $this->_convert_custom_date_to_database_date($date);
        }

        $db = \Config\Database::connect();
        $project_time_table = $db->prefixTable('project_time');
        $projects_table = $db->prefixTable('projects');
        $tasks_table = $db->prefixTable('tasks');
        $task_status_table = $db->prefixTable('task_status');

        $offset = convert_seconds_to_time_format(get_timezone_offset());

        // Query all timelogs on that date
        $sql = "SELECT pt.*, p.title AS project_title, t.title AS task_title, ts.title AS task_status_title, ts.key_name AS task_status_key
                FROM $project_time_table pt
                JOIN $projects_table p ON p.id = pt.project_id
                LEFT JOIN $tasks_table t ON t.id = pt.task_id
                LEFT JOIN $task_status_table ts ON ts.id = t.status_id
                WHERE pt.deleted = 0
                  AND DATE(ADDTIME(pt.start_time, '$offset')) = '$date'
                ORDER BY p.title ASC, pt.id ASC";

        $logs = $db->query($sql)->getResult();

        // Group by project_id
        $grouped = [];
        foreach ($logs as $log) {
            $pid = $log->project_id;
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'project_id' => $pid,
                    'project_title' => $log->project_title,
                    'tasks_list' => [],
                    'statuses' => [],
                    'next_action' => [],
                    'key_milestone' => [],
                    'challenges' => [],
                    'solution' => [],
                    'upcoming_priorities' => [],
                    'remarks' => []
                ];
            }

            // Parse note fields
            $parsed = $this->_parse_note_fields($log->note);

            // Construct task description (Task Title + Note)
            $task_desc = "";
            if ($log->task_title) {
                $task_desc .= "<strong>" . htmlspecialchars($log->task_title) . "</strong>";
            }
            if ($parsed['tasks']) {
                if ($task_desc) {
                    $task_desc .= ": ";
                }
                $task_desc .= htmlspecialchars($parsed['tasks']);
            }
            if ($task_desc) {
                $grouped[$pid]['tasks_list'][] = $task_desc;
            }

            // Task status
            if ($log->task_status_title) {
                $status_class = "bg-secondary";
                $status_lower = strtolower($log->task_status_key ?? '');
                if ($status_lower === "done" || $status_lower === "completed" || $status_lower === "closed") {
                    $status_class = "bg-success";
                } elseif ($status_lower === "in_progress" || $status_lower === "in progress") {
                    $status_class = "bg-warning text-dark";
                } elseif ($status_lower === "to_do" || $status_lower === "to do") {
                    $status_class = "bg-info text-dark";
                }
                $status_badge = '<span class="badge ' . $status_class . '" style="font-size:11px;">' . htmlspecialchars($log->task_status_title) . '</span>';
                if (!in_array($status_badge, $grouped[$pid]['statuses'])) {
                    $grouped[$pid]['statuses'][] = $status_badge;
                }
            }

            // Accumulate other fields
            if ($parsed['next_action']) $grouped[$pid]['next_action'][] = htmlspecialchars($parsed['next_action']);
            if ($parsed['key_milestone']) $grouped[$pid]['key_milestone'][] = htmlspecialchars($parsed['key_milestone']);
            if ($parsed['challenges']) $grouped[$pid]['challenges'][] = htmlspecialchars($parsed['challenges']);
            if ($parsed['solution']) $grouped[$pid]['solution'][] = htmlspecialchars($parsed['solution']);
            if ($parsed['upcoming_priorities']) $grouped[$pid]['upcoming_priorities'][] = htmlspecialchars($parsed['upcoming_priorities']);
            if ($parsed['remarks']) $grouped[$pid]['remarks'][] = htmlspecialchars($parsed['remarks']);
        }

        // Format rows
        $result = [];
        $sl = 1;
        foreach ($grouped as $data) {
            $project_link = '<a href="' . get_uri("projects/view/" . $data['project_id']) . '" target="_blank" style="font-weight:600; color:inherit; text-decoration:none;">' . htmlspecialchars($data['project_title']) . '</a>';

            // Tasks list with bullet points if multiple, otherwise single line
            $tasks_html = "";
            $unique_tasks = array_unique($data['tasks_list']);
            if (count($unique_tasks) > 1) {
                $tasks_html = '<ul style="margin:0; padding-left:15px;">' . implode('', array_map(function($t) { return '<li>' . $t . '</li>'; }, $unique_tasks)) . '</ul>';
            } elseif (count($unique_tasks) === 1) {
                $tasks_html = $unique_tasks[0];
            } else {
                $tasks_html = '-';
            }

            // Status list
            $status_html = implode('<br />', $data['statuses']);
            if (!$status_html) $status_html = '-';

            $result[] = [
                'sl' => $sl++,
                'project_title' => $project_link,
                'tasks' => $tasks_html,
                'status' => $status_html,
                'next_action' => nl2br(implode("<br />", array_unique($data['next_action']))),
                'key_milestone' => nl2br(implode("<br />", array_unique($data['key_milestone']))),
                'challenges' => nl2br(implode("<br />", array_unique($data['challenges']))),
                'solution' => nl2br(implode("<br />", array_unique($data['solution']))),
                'upcoming_priorities' => nl2br(implode("<br />", array_unique($data['upcoming_priorities']))),
                'remarks' => nl2br(implode("<br />", array_unique($data['remarks'])))
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $result
        ]);
    }

    private function _parse_note_fields($note)
    {
        $note = strip_tags($note); // Strip any HTML tags first to get clean text

        $sections = [
            'tasks' => '',
            'next_action' => '',
            'key_milestone' => '',
            'challenges' => '',
            'solution' => '',
            'upcoming_priorities' => '',
            'remarks' => ''
        ];

        // Define regex patterns for headers (case-insensitive)
        $patterns = [
            'next_action' => '/(?:next\s*actions?|next):/i',
            'key_milestone' => '/(?:key\s*milestones?|milestones?):/i',
            'challenges' => '/(?:challenges?|address\s*challenges?):/i',
            'solution' => '/(?:solutions?):/i',
            'upcoming_priorities' => '/(?:upcoming\s*priorities|priorities):/i',
            'remarks' => '/(?:remarks?):/i',
            'tasks' => '/(?:tasks?|work\s*done|updates?):/i'
        ];

        // We want to find the positions of all matches
        $matches = [];
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $note, $match, PREG_OFFSET_CAPTURE)) {
                $matches[] = [
                    'key' => $key,
                    'pos' => $match[0][1],
                    'len' => strlen($match[0][0])
                ];
            }
        }

        // Sort matches by their starting position
        usort($matches, function($a, $b) {
            return $a['pos'] - $b['pos'];
        });

        if (empty($matches)) {
            // No headers found: put everything under tasks
            $sections['tasks'] = trim($note);
        } else {
            // There are matches. The text before the first match is implicitly part of 'tasks'
            // unless the first match is at position 0.
            $first_pos = $matches[0]['pos'];
            if ($first_pos > 0) {
                $sections['tasks'] = trim(substr($note, 0, $first_pos));
            }

            // Extract each section
            $cnt = count($matches);
            for ($i = 0; $i < $cnt; $i++) {
                $curr = $matches[$i];
                $start = $curr['pos'] + $curr['len'];
                
                if ($i < $cnt - 1) {
                    $end = $matches[$i + 1]['pos'];
                    $length = $end - $start;
                    $content = substr($note, $start, $length);
                } else {
                    $content = substr($note, $start);
                }
                
                $sections[$curr['key']] = trim($content);
            }
        }

        return $sections;
    }

    private function _convert_custom_date_to_database_date($date)
    {
        if (!$date) return date('Y-m-d');
        
        $date = trim($date);
        
        // Already in standard Y-m-d format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $format = get_setting("date_format");
        if ($format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return $d->format("Y-m-d");
            }
        }

        // Try standard split formats manually (DD-MM-YYYY or MM-DD-YYYY)
        $parts = preg_split('/[-.\/]/', $date);
        if (count($parts) === 3) {
            if (strlen($parts[0]) === 2 && strlen($parts[1]) === 2 && strlen($parts[2]) === 4) {
                // If it is DD-MM-YYYY or MM-DD-YYYY. Default to DD-MM-YYYY
                return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } elseif (strlen($parts[0]) === 4 && strlen($parts[1]) === 2 && strlen($parts[2]) === 2) {
                // YYYY-MM-DD but different separator
                return $parts[0] . '-' . $parts[1] . '-' . $parts[2];
            }
        }

        $timestamp = strtotime($date);
        if ($timestamp) {
            return date("Y-m-d", $timestamp);
        }

        return $date;
    }
}
