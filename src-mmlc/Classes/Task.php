<?php
namespace RobinTheHood\ModifiedCsvImporter\Classes;

class Task
{
    protected $id;
    protected $logValues = [];

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setLogValues($values)
    {
        foreach($values as $index => $value) {
            $this->logValues[$index] = $value;
        }
    }

    public function log($str)
    {
        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/task_log_' . $this->id . '.txt';
        file_put_contents($fileName, $str);
    }

    public function logValues()
    {
        $this->log(json_encode($this->logValues));
    }

    public function pushLog($id)
    {
        $fileName = 'task_log_' . $id . '.txt';
        $str = file_get_contents($fileName);
        echo $str;
    }
}
