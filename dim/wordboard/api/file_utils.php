<?php

function readJSONFile($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }
    
    $filesize = filesize($filepath);
    if ($filesize === false || $filesize === 0) {
        return array();
    }
    
    $fp = fopen($filepath, 'r');
    if (!$fp) {
        return null;
    }
    
    if (flock($fp, LOCK_SH)) {
        $content = fread($fp, $filesize);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        if ($content === false || strlen($content) === 0) {
            return array();
        }
        
        $data = json_decode($content, true);
        return $data;
    }
    
    fclose($fp);
    return null;
}

function writeJSONFile($filepath, $data) {
    if (empty($filepath)) {
        return false;
    }
    
    $fp = fopen($filepath, 'c');
    if (!$fp) {
        return false;
    }
    
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    
    fclose($fp);
    return false;
}

function appendJSONFile($filepath, $key, $value) {
    $data = readJSONFile($filepath);
    if ($data === null) {
        $data = array();
    }
    
    $data[$key] = $value;
    return writeJSONFile($filepath, $data);
}

function rebuildActivityIndex($activitiesDir = 'admin/activities/') {
    $types = array('quiz', 'match', 'wheel', 'crossword', 'wordsearch', 'missingword', 'groupsort');
    $index = array();

    foreach ($types as $type) {
        $typeDir = $activitiesDir . $type . '/';
        if (!is_dir($typeDir)) continue;

        $files = glob($typeDir . '*.json');
        foreach ($files as $file) {
            $activity = readJSONFile($file);
            if ($activity !== null) {
                $id = $activity['id'];
                $index[$id] = array(
                    'title' => isset($activity['title']) ? $activity['title'] : '',
                    'type' => isset($activity['type']) ? $activity['type'] : $type,
                    'tags' => isset($activity['tags']) ? $activity['tags'] : array(),
                    'created_by' => isset($activity['created_by']) ? $activity['created_by'] : '',
                    'created_date' => isset($activity['created_date']) ? $activity['created_date'] : ''
                );
            }
        }
    }

    $indexPath = $activitiesDir . 'index.json';
    writeJSONFile($indexPath, $index);
    return count($index);
}

function getActivityIndex($activitiesDir = 'admin/activities/') {
    $indexPath = $activitiesDir . 'index.json';
    if (!file_exists($indexPath)) {
        rebuildActivityIndex($activitiesDir);
    }
    return readJSONFile($indexPath);
}

function updateIndexEntry($id, $activity, $activitiesDir = 'admin/activities/') {
    $indexPath = $activitiesDir . 'index.json';
    $index = readJSONFile($indexPath);
    if ($index === null) {
        $index = array();
    }
    $index[$id] = array(
        'title' => isset($activity['title']) ? $activity['title'] : '',
        'type' => isset($activity['type']) ? $activity['type'] : '',
        'tags' => isset($activity['tags']) ? $activity['tags'] : array(),
        'created_by' => isset($activity['created_by']) ? $activity['created_by'] : '',
        'created_date' => isset($activity['created_date']) ? $activity['created_date'] : ''
    );
    writeJSONFile($indexPath, $index);
}

function removeIndexEntry($id, $activitiesDir = 'admin/activities/') {
    $indexPath = $activitiesDir . 'index.json';
    $index = readJSONFile($indexPath);
    if ($index !== null && isset($index[$id])) {
        unset($index[$id]);
        writeJSONFile($indexPath, $index);
    }
}

function extractTypeFromId($id) {
    $types = array('quiz', 'match', 'wheel', 'crossword', 'wordsearch', 'missingword', 'groupsort');
    foreach ($types as $type) {
        if (strpos($id, '-' . $type . '-') !== false) {
            return $type;
        }
    }
    return null;
}

function findActivityFile($id, $activitiesDir = 'admin/activities/') {
    $type = extractTypeFromId($id);
    if ($type !== null) {
        $filePath = $activitiesDir . $type . '/' . $id . '.json';
        if (file_exists($filePath)) {
            return $filePath;
        }
    }

    $types = array('quiz', 'match', 'wheel', 'crossword', 'wordsearch', 'missingword', 'groupsort');
    foreach ($types as $t) {
        $filePath = $activitiesDir . $t . '/' . $id . '.json';
        if (file_exists($filePath)) {
            return $filePath;
        }
    }
    return null;
}
