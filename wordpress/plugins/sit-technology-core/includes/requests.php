<?php
if (!defined('ABSPATH')) { exit; }

function sit_core_request_types() {
    return array('enquiry'=>'General enquiry', 'consultation'=>'Consultation', 'software-project'=>'Software project', 'dedicated-team'=>'Dedicated team');
}

// Field order is deliberate: it makes retries independent of incoming JSON key order.
function sit_core_request_fields($type) {
    $fields = array(
        'enquiry'=>array(),
        'consultation'=>array(
            'contact_format'=>array('label'=>'Preferred conversation', 'options'=>array('video'=>'Video call','phone'=>'Phone call','email'=>'Email')),
            'timezone'=>array('label'=>'Your time zone', 'max'=>80, 'min'=>2),
            'availability'=>array('label'=>'Availability', 'max'=>300, 'min'=>0),
        ),
        'software-project'=>array(
            'project_stage'=>array('label'=>'Project stage', 'options'=>array('idea'=>'An idea to explore','prototype'=>'A prototype to develop','existing'=>'An existing product or system')),
            'product_type'=>array('label'=>'Product or system', 'options'=>array('web'=>'Web application','mobile'=>'Mobile application','platform'=>'Business platform','integration'=>'Systems integration','other'=>'Let’s work it out together')),
        ),
        'dedicated-team'=>array(
            'roles'=>array('label'=>'Roles and skills', 'max'=>600, 'min'=>3),
            'team_size'=>array('label'=>'Team size', 'options'=>array('1'=>'One specialist','2-3'=>'2–3 specialists','4-6'=>'4–6 specialists','7-plus'=>'7+ specialists','discuss'=>'Help us shape the team')),
            'engagement_length'=>array('label'=>'Expected duration', 'options'=>array('under-3'=>'Under 3 months','3-6'=>'3–6 months','6-plus'=>'6+ months','discuss'=>'To be discussed')),
        ),
    );
    $context = array(
        'audience'=>array('label'=>'Intended users', 'max'=>500, 'min'=>0, 'optional'=>true),
        'systems'=>array('label'=>'Existing systems', 'max'=>600, 'min'=>0, 'optional'=>true),
        'integrations'=>array('label'=>'Integrations', 'max'=>600, 'min'=>0, 'optional'=>true),
        'outcomes'=>array('label'=>'Desired outcomes', 'max'=>800, 'min'=>0, 'optional'=>true),
        'constraints'=>array('label'=>'Delivery requirements', 'max'=>800, 'min'=>0, 'optional'=>true),
    );
    return array_merge($fields[$type] ?? array(), $context);
}

function sit_core_detail_values($row) {
    $details = json_decode($row->details ?? '', true);
    $result = array();
    foreach (sit_core_request_fields($row->request_type ?? 'enquiry') as $key=>$spec) {
        $value = is_array($details) ? ($details[$key] ?? '') : '';
        if (!is_string($value)) { $value = ''; }
        if (!empty($spec['optional']) && $value === '') { continue; }
        $result[$spec['label']] = $spec['options'][$value] ?? $value;
    }
    return $result;
}

function sit_core_statuses() {
    return array('new'=>'New','reviewing'=>'Reviewing','waiting'=>'Awaiting reply','proposal'=>'Proposal','won'=>'Agreed','closed'=>'Closed');
}

function sit_core_allowed_statuses($current) {
    $next = array(
        'new'=>array('new','reviewing','closed'),
        'reviewing'=>array('reviewing','waiting','proposal','closed'),
        'waiting'=>array('waiting','reviewing','closed'),
        'proposal'=>array('proposal','reviewing','won','closed'),
        'won'=>array('won','closed'),
        'closed'=>array('closed','reviewing'),
    );
    return array_intersect_key(sit_core_statuses(), array_flip($next[$current] ?? array()));
}

// The version detects concurrent edits even when status and assignee change back.
function sit_core_can_update($row, $version, $status) {
    return $row && $version > 0 && (int)$row->version === $version && isset(sit_core_allowed_statuses($row->status)[$status]);
}
