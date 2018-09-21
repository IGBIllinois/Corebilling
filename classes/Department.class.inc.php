<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 12/5/13
 * Time: 10:52 AM
 */

class Department {

    private $db;
    
    private $departmentName;
    private $departmentId;
    private $description;


    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function __destruct()
    {

    }

    /** Add a new department
     * @param $departmentName
     * @param $description
     */
    public function AddDepartment($departmentName, $description)
    {
        $queryAddDepartment= "INSERT INTO departments (department_name, description)VALUES(:department_name,:description)";
        $addDepartmentPrep = $this->db->prepare($queryAddDepartment);
        $addDepartmentPrep->execute(array(':department_name'=>$departmentName,':description'=>$description));
        $departmentId = $this->db->lastInsertId();
        $this->departmentName = $departmentName;
        $this->description = $description;
        $this->departmentId = $departmentId;
		log::log_message("Added department '$departmentName'");
    }

    /**
     * Update department row in database with changes made to this object
     */
    public function UpdateDepartment()
    {
        $queryUpdateDepartment = "UPDATE departments SET
                                department_name=\"".$this->departmentName."\",
                                description=\"".$this->description."\"
                                WHERE id=".$this->departmentId;
        $this->db->exec($queryUpdateDepartment);
    }

    /** Load department by id from database into this object
     * @param $id
     */
    public function LoadDepartment($id)
    {
        $queryDepartmentById = "SELECT department_name,id,department_code FROM departments WHERE id=:id";
        $departmentInfo = $this->db->prepare($queryDepartmentById);
        $departmentInfo->execute(array(':id'=>$id));
        $departmentInfoArr = $departmentInfo->fetch(PDO::FETCH_ASSOC);
        $this->departmentName = $departmentInfoArr["department_name"];
        $this->departmentCode = $departmentInfoArr["department_code"];
        $this->departmentId = $departmentInfoArr["id"];
    }

    /** Get a list of available departments
     * @return array
     */
    public function GetDepartmentList()
    {
        $queryDepartmentList= "SELECT department_name,id FROM departments ORDER BY department_name";
        $departmentInfo = $this->db->query($queryDepartmentList);
        return $departmentInfo->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Check if a department name already exists in the database
     * @param $departmentName
     * @return bool
     */
    public function Exists($departmentName)
    {
        $queryDepartment= "SELECT COUNT(*) FROM departments WHERE department_name=:department_name";
        $department= $this->db->prepare($queryDepartment);
        $department->execute(array(':department_name'=>$departmentName));
        $departmentCount = $department->fetchColumn();

        if($departmentCount)
        {
            return true;
        }
        return false;
    }

    /**Get all members of this department
     * @return array
     */
    public function GetMembers()
    {
        if($this->getDepartmentId())
        {
            $user = new User($this->db);
            $departmentMembers = $user->GetDepartmentUsers($this->departmentId);
            return $departmentMembers;
        }
        return array();
    }
    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
	    if($this->description != $description){
	        $this->description = $description;
	        log::log_message("Set description of department '".$this->departmentName."' to '$description'");
	    }
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $departmentId
     */
    public function setDepartmentId($departmentId)
    {
	    if($this->departmentId != $departmentId){
	        $this->departmentId = $departmentId;
	        log::log_message("Set id of department '".$this->departmentName."' to $departmentId");
	    }
    }

    /**
     * @return mixed
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @param mixed $departmentName
     */
    public function setDepartmentName($departmentName)
    {
	    if($this->departmentName != $departmentName){
		    log::log_message("Set name of department '".$this->departmentName."' to '$departmentName'");
			$this->departmentName = $departmentName;
		}
    }

    /**
     * @return mixed
     */
    public function getDepartmentName()
    {
        return $this->departmentName;
    }
} 