<?php

/**
 * Project
 *
 * @package Project
 */
class Project {
    protected $project_id;
    protected $name;
    protected $description;
    protected $website;
    protected $budget;
    protected $repository;
    protected $contact_info;
    protected $last_commit;
    protected $active;
    protected $owner_id;
    protected $fund_id;
    protected $testflight_enabled;
    protected $testflight_team_token;
    protected $logo;
    protected $cr_anyone;
    protected $cr_project_admin;
    protected $cr_3_favorites;
    protected $cr_job_runner;
    protected $cr_users_specified;
    protected $internal;
    protected $repo_type;
    protected $creation_date;
    protected $hipchat_enabled;
    protected $hipchat_notification_token;
    protected $hipchat_room;
    protected $hipchat_color;
    protected $github_repo_url;
    protected $github_id;
    protected $github_secret;

    public function __construct($id = null) {
        if (!mysql_connect(DB_SERVER, DB_USER, DB_PASSWORD)) {
            throw new Exception('Error: ' . mysql_error());
        }
        if (!mysql_select_db(DB_NAME)) {
            throw new Exception('Error: ' . mysql_error());
        }
        if ($id !== null) {
            $this->load($id);
        }
    }

    static public function getById($project_id) {
        $project = new Project();
        $project->loadById($project_id);
        return $project;
    }

    public function loadById($id) {
        return $this->load($id);
    }
    
    public function loadByName($name) {
        $query = "SELECT project_id FROM `".PROJECTS."` WHERE `name`='" . $name . "'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $project_id = $row['project_id'];
            $this->load($project_id);
        } else {
            throw new Exception('There is no project by that name (' . $name . ')');
        }
    }

    public function loadByRepo($repo) {
        $query = "SELECT `project_id` FROM `".PROJECTS."` WHERE `repository`='" . $repo . "'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $project_id = $row['project_id'];
            $this->load($project_id);
        } else {
            throw new Exception('There is no project with that repository');
        }
    }

    protected function load($project_id = null) {
        if ($project_id === null && ! $this->project_id) {
            throw new Exception('Missing project id.');
        } elseif ($project_id === null) {
            $project_id = $this->project_id;
        }

        $query = "
            SELECT 
                p.project_id,
                p.name,
                p.description,
                p.website,
                p.budget,
                p.repository,
                p.repo_type,
                p.contact_info,
                p.last_commit,
                p.active,
                p.owner_id, 
                p.fund_id,
                p.testflight_enabled,
                p.testflight_team_token,
                p.logo,
                p.cr_anyone,
                p.cr_3_favorites,
                p.cr_project_admin,
                p.cr_job_runner,
                p.cr_users_specified,
                p.internal, 
                p.creation_date,
                p.hipchat_enabled,
                p.hipchat_notification_token,
                p.hipchat_room,
                p.hipchat_color,
                p.github_id,
                p.github_secret
            FROM  ".PROJECTS. " as p
            WHERE p.project_id = '" . (int)$project_id . "'";
        $res = mysql_query($query);

        if (!$res) {
            throw new Exception('MySQL error.');
        }

        $row = mysql_fetch_assoc($res);
        if (! $row) {
            throw new Exception('Invalid project id.');
        }

        $this->setProjectId($row['project_id'])
             ->setName($row['name'])
             ->setDescription($row['description'])
             ->setWebsite($row['website'])
             ->setBudget($row['budget'])
             ->setRepository($row['repository'])
             ->setRepo_type($row['repo_type'])
             ->setContactInfo($row['contact_info'])
             ->setLastCommit($row['last_commit'])
             ->setActive($row['active'])
             ->setTestFlightEnabled($row['testflight_enabled'])
             ->setTestFlightTeamToken($row['testflight_team_token'])
             ->setLogo($row['logo'])
             ->setOwnerId($row['owner_id'])
             ->setFundId($row['fund_id']);
             $this->setCrAnyone($row['cr_anyone']);
             $this->setCrFav($row['cr_3_favorites']);
             $this->setCrAdmin($row['cr_project_admin']);
             $this->setCrRunner($row['cr_job_runner']);
             $this->setCrUsersSpecified($row['cr_users_specified']);
             $this->setInternal($row['internal']);
             $this->setCreationDate($row['creation_date']);
        $this->setHipchatEnabled($row['hipchat_enabled']);
        $this->setHipchatNotificationToken($row['hipchat_notification_token']);
        $this->setHipchatRoom($row['hipchat_room']);
        $this->setHipchatColor($row['hipchat_color']);
        $this->setGithubId($row['github_id']);
        $this->setGithubSecret($row['github_secret']);
        return true;
    }
    
    public function getTotalFees($project_id) {
        $feesCount = 0;
        $feesQuery = "SELECT SUM(F.amount) AS fees_sum FROM " . FEES . " F,
                     " . WORKLIST . " W
                     WHERE F.worklist_id = W.id
                     AND W.project_id = " . $project_id  . "
                     AND F.withdrawn = 0
                     AND W.status IN ('Completed', 'Done')";
        $feesQueryResult = mysql_query($feesQuery);
        if (mysql_num_rows($feesQueryResult)) {
            $feesCountArray = mysql_fetch_array($feesQueryResult);
            if ($feesCountArray['fees_sum']) {
                $feesCount = number_format($feesCountArray['fees_sum'], 0, '', ',');
            }
        }
        return $feesCount;
    }
    
    public function idExists($project_id) {
        $query = "
            SELECT project_id
            FROM ".PROJECTS."
            WHERE project_id=".(int)$project_id;
        
        $res = mysql_query($query);
        if (!$res) {
            throw new Exception('MySQL error.');
        }
        $row = mysql_fetch_row($res);
        return (boolean)$row[0];
    }

    public function setProjectId($project_id) {
        $this->project_id = (int)$project_id;
        return $this;
    }

    public function getProjectId() {
        return $this->project_id;
    }
    
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function getDescription() {
        return $this->description;
    }    

    public function setWebsite($website) {
        $this->website = $website;
        return $this;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function getWebsiteLink() {
        return linkify($this->website, null, false, true);
    }

    public function getWebsiteUrl() {
        if (strpos($this->getWebsite(), "http") === 0) {
            return $this->getWebsite();
        } else {
            return "http://" . $this->getWebsite();
        }
    }
    
    public function setBudget($budget) {
        $this->budget = $budget;
        return $this;
    }
    
    public function getBudget() {
        return $this->budget;
    }
       
    public function setRepository($repository) {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository() {
        return $this->repository;
    }
    
    public function setRepo_type($repo_type) {
        $this->repo_type = $repo_type;
        return $this;
    }

    public function getRepo_type() {
        return $this->repo_type;
    }        
    
    public function setContactInfo($contact_info) {
        $this->contact_info = $contact_info;
        return $this;
    }
    
    public function getContactInfo() {
        return $this->contact_info;
    }
    
    public function setLastCommit($date) {
        $this->last_commit = $date;
        return $this;
    }
    
    public function getLastCommit() {
        return $this->last_commit;
    }

    public function setActive($active) {
        $this->active = $active;
        return $this;
    }

    public function getActive() {
        return $this->active;
    }    

    public function setOwnerId($owner_id) {
        $this->owner_id = $owner_id;
        return $this;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }
    
    public function setFundId($fund_id) {
        $this->fund_id = $fund_id;
    }

    public function getFundId() {
        return $this->fund_id;
    }
    
    public function setTestFlightTeamToken($testflight_team_token) {
        $this->testflight_team_token = $testflight_team_token;
        return $this;
    }

    public function getTestFlightTeamToken() {
        return $this->testflight_team_token;
    }

    public function setTestFlightEnabled($testflight_enabled) {
        $this->testflight_enabled = $testflight_enabled;
        return $this;
    }

    public function getTestFlightEnabled() {
        return $this->testflight_enabled;
    }

    public function setLogo($logo) {
        $this->logo = $logo;
        return $this;
    }

    public function getLogo() {
        return $this->logo;
    } 

    public function setCrAnyone($cr_anyone) {
        $this->cr_anyone = $cr_anyone;
        return $this;
    }

    public function getCrAnyone() {
        return $this->cr_anyone;
    } 
    
    public function setCrFav($cr_3_favorites) {
        $this->cr_3_favorites = $cr_3_favorites;
        return $this;
    }

    public function getCrFav() {
        return $this->cr_3_favorites;
    }

    public function setCrAdmin($cr_project_admin) {
        $this->cr_project_admin = $cr_project_admin;
        return $this;
    }

    public function getCrAdmin() {
        return $this->cr_project_admin;
    }
    
    public function setCrRunner($cr_job_runner) {
        $this->cr_job_runner = $cr_job_runner;
        return $this;
    }
    public function setCrUsersSpecified($cr_users_specified) {
        $this->cr_users_specified = $cr_users_specified;
    }
    public function getCrRunner() {
        return $this->cr_job_runner;
    }
    public function getCrUsersSpecified() {
        return $this->cr_users_specified;
    }
    public function setInternal($internal) {
        $this->internal = $internal ? 1 : 0;
        return $this;
    }

    public function getInternal() {
        return $this->internal;
    }
    
    public function setCreationDate($creation_date) {
        $this->creation_date = $creation_date;
        return $this;
    }
    
    public function getCreationDate() {
        return $this->creation_date;
    }
    
    public function setHipchatNotificationToken($hipchat_notification_token) {
        $this->hipchat_notification_token = $hipchat_notification_token;
        return $this;
    }

    public function getHipchatNotificationToken() {
        return $this->hipchat_notification_token;
    }

    public function setHipchatEnabled($hipchat_enabled) {
        $this->hipchat_enabled = $hipchat_enabled;
        return $this;
    }

    public function getHipchatEnabled() {
        return $this->hipchat_enabled;
    }
    public function setHipchatRoom($hipchat_room) {
        $this->hipchat_room = $hipchat_room;
        return $this;
    }

    public function getHipchatRoom() {
        return $this->hipchat_room;
    }
    
    public function setHipchatColor($hipchat_color) {
        $this->hipchat_color = $hipchat_color;
        return $this;
    }
    
    public function getHipchatColor() {
        $hipchat_color = $this->hipchat_color;
        
        if (in_array($hipchat_color, $this->getHipchatColorsArray())) {
            return $hipchat_color;
        }
        
        return $this->getHipchatDefaultColor();
    }

    public function getGithubId() {
        return $this->github_id;
    }

    public function setGithubId($github_id) {
        $this->github_id = $github_id;
    }

    public function getGithubSecret() {
        return $this->github_secret;
    }

    public function setGithubSecret($github_secret) {
        $this->github_secret = $github_secret;
    }

    public function getHipchatColorsArray() {
        return array(
             "yellow",
             "red",
             "green",
             "purple",
             "gray",
             "random"
        );
    }
    
    public function getHipchatDefaultColor() {
        $colors = $this->getHipchatColorsArray();
        return $colors[0];
    }

    public function sendHipchat_notification($message, $message_format='html', $notify=0) {
        $success = true;
        $room_id = 0;
        $token = $this->getHipchatNotificationToken();
        $url = HIPCHAT_API_AUTH_URL . $token;
        
        $response = CURLHandler::Get($url, array());
        $response = json_decode($response);

        if (count($response->rooms)) {
            foreach($response->rooms as $key => $room) {
                if ($room->name == trim($this->getHipchatRoom())) {
                    $room_id = $room->room_id;
                    break;
                }
            }
            
            if ($room_id > 0 ) {
                $url = HIPCHAT_API_MESSAGE_URL . $token;
                $fields = array( 
                    'room_id' => $room_id,
                    'from' => 'Worklist.net',
                    'message' => $message,
                    'message_format' => $message_format,
                    'notify' => $notify,
                    'color' => $this->getHipchatColor()
                );
                
                $result = CURLHandler::Post($url, $fields);
                $result = json_decode($result);
                if ($result->status != 'sent') {
                    $success = false;
                    $body = "Failed to send message: " . $message;
                }
            } else {
                    $success = false;
                    $body = "Failed to find room " . $this->getHipchatRoom() . ".";
            }
        } else {
            $success = false;
            $body = "Failed to authenticate to hipchat.";            
        }

        if ($success == false) {
            $email = $this->getContactInfo();
            $subject = "HipChat Notification Failed";
            if (!send_email($email, $subject, $body, $body, array('Cc' => OPS_EMAIL))) {
               error_log("project-class.php: sendHipchat_notification : send_email failed");
            }
        }
    }

    protected function insert() {
        $query = "INSERT INTO " . PROJECTS . "
            (name, description, website, budget, repository, contact_info, active,
                owner_id, testflight_enabled, testflight_team_token,
                logo, last_commit, cr_anyone, cr_3_favorites, cr_project_admin,
                cr_job_runner,cr_users_specified, internal, creation_date, hipchat_enabled,
                hipchat_notification_token, hipchat_room, hipchat_color, repo_type, github_id, github_secret) " .
            "VALUES (".
            "'".mysql_real_escape_string($this->getName())."', ".
            "'".mysql_real_escape_string($this->getDescription())."', ".
            "'".mysql_real_escape_string($this->getWebsite()) . "', " .
            "'".mysql_real_escape_string($this->getBudget())."', ".
            "'".mysql_real_escape_string($this->getRepository())."', ".
            "'".mysql_real_escape_string($this->getContactInfo())."', ".
            "'".mysql_real_escape_string($this->getActive())."', ".
            "'".mysql_real_escape_string($this->getOwnerId())."', ".
            "'".mysql_real_escape_string($this->getTestFlightEnabled())."', ".
            "'".mysql_real_escape_string($this->getTestFlightTeamToken())."', ".
            "'".mysql_real_escape_string($this->getLogo())."', ".
            "NOW(), ".
            "1, ".
            "'".intval($this->getCrFav())."', ".
            "'".intval($this->getCrAdmin())."', ".
            "'" . intval($this->getCrRunner()) . "', " .
            "'" . intval($this->getCrUsersSpecified()) . "', " .
            "'" . intval($this->getInternal()) . "', " .
            "NOW(), " .
            "'" . intval($this->getHipchatEnabled()) . "', " .
            "'" . mysql_real_escape_string($this->getHipchatNotificationToken()) . "', " .
            "'" . mysql_real_escape_string($this->getHipchatRoom()) . "', " .
            "'" . mysql_real_escape_string($this->getHipchatColor()) . "', " .
            "'" . mysql_real_escape_string($this->getRepo_type()) . "', " .
            "'" . mysql_real_escape_string($this->getGithubId()) . "', " .
            "'" . mysql_real_escape_string($this->getGithubSecret()) . "')";
        $rt = mysql_query($query);
        $project_id = mysql_insert_id();
        $this->setProjectId($project_id);
                
        //for the project added insert 3 pre-populated roles with percentages and minimum amounts <joanne>
        $query = "INSERT INTO " . ROLES . " (project_id, role_title, percentage, min_amount)
            VALUES 
            ($project_id,'Creator','10.00','10.00'),
            ($project_id,'Runner','25.00','20.00'),
            ($project_id,'Reviewer','10.00','5.00')";
        $rt = mysql_query($query);
        
        $query = "INSERT INTO " . PROJECT_RUNNERS . " (project_id, runner_id)
            VALUES 
            ($project_id, ' " . mysql_real_escape_string($this->getOwnerId()) . " ')";
        $rt = mysql_query($query);    
        if($rt) {
            return 1;
        }
        return 0;
     }

    protected function update() {

        $query = "
            UPDATE ".PROJECTS." 
            SET
                name='".mysql_real_escape_string($this->getName())."',
                description='".mysql_real_escape_string($this->getDescription())."',
                website='" . mysql_real_escape_string($this->getWebsite()) . "',
                budget='".mysql_real_escape_string($this->getBudget())."',
                repository='" .mysql_real_escape_string($this->getRepository())."',
                contact_info='".mysql_real_escape_string($this->getContactInfo())."',
                last_commit='".mysql_real_escape_string($this->getLastCommit())."',
                testflight_enabled='".mysql_real_escape_string($this->getTestFlightEnabled())."',
                testflight_team_token='".mysql_real_escape_string($this->getTestFlightTeamToken())."',
                logo='".mysql_real_escape_string($this->getLogo())."',
                active='".intval($this->getActive())."',
                owner_id='".intval($this->getOwnerId())."',
                cr_anyone='".intval($this->getCrAnyone())."',
                cr_3_favorites='".intval($this->getCrFav())."',
                cr_project_admin='".intval($this->getCrAdmin())."',
                cr_job_runner='" . intval($this->getCrRunner()) . "',
                cr_users_specified='" . intval($this->getCrUsersSpecified()) . "',
                internal='" . intval($this->getInternal()) . "',
                hipchat_enabled='" . intval($this->getHipchatEnabled()) . "',
                hipchat_notification_token='" . mysql_real_escape_string($this->getHipchatNotificationToken()) . "',
                hipchat_room='" . mysql_real_escape_string($this->getHipchatRoom()) . "',
                hipchat_color='" . mysql_real_escape_string($this->getHipchatColor()) . "'
            WHERE project_id=" . $this->getProjectId();
        $result = mysql_query($query);
        return $result ? 1 : 0;
    }

    public function save() {
        if (isset($this->project_id)) {
            if ($this->idExists($this->getProjectId())) {
                return $this->update();
            } else {
                return $this->insert();
            }
        } else {
            return $this->insert();
        }
    }

    /**
        Return an array of all projects containing all fields
    **/    
    public function getProjects($active = true, $selections = array(), $onlyInactive = false) {
        $query = "
            SELECT
                " . ((count($selections) > 0) ? implode(",", $selections) : "*") . "
            FROM
                `" . PROJECTS . "`" ."
            WHERE 
                `" . PROJECTS . "`.internal = 1  AND `" . PROJECTS . "`.active = 1
            ORDER BY name ASC";
        
        $result = mysql_query($query);
        
        if (mysql_num_rows($result)) {
            while ($project = mysql_fetch_assoc($result)) {
                $query = "SELECT
                            SUM(status IN ('Done', 'Completed')) AS completed, 
                            SUM(status IN ('Working', 'Review', 'SvnHold', 'Functional')) AS underway, 
                            SUM(status='Bidding') AS bidding 
                          FROM
                            " . WORKLIST . " 
                          WHERE
                            project_id = " . $project['project_id'];
                $resultCount = mysql_query($query);
                $resultCount = mysql_fetch_object($resultCount);

                $feesCount = 0;
                $bCount = $resultCount->bidding === NULL ? 0 : $resultCount->bidding;
                $uCount = $resultCount->underway === NULL ? 0 : $resultCount->underway;
                $cCount = $resultCount->completed === NULL ? 0 : $resultCount->completed;
    
                if($cCount) {
                    $feesQuery = "SELECT SUM(F.amount) as fees_sum FROM " . FEES . " F,
                            " . WORKLIST . " W
                            WHERE F.worklist_id = W.id 
                            AND F.withdrawn = 0
                            AND W.project_id = " . $project['project_id'] . "
                            AND W.status IN ('Completed', 'Done')";
                    $feesQueryResult = mysql_query($feesQuery);
                    if (mysql_num_rows($feesQueryResult)) {
                        $feesCountArray = mysql_fetch_array($feesQueryResult);
                        if($feesCountArray['fees_sum']) {
                            $feesCount = number_format($feesCountArray['fees_sum'],0,'',',');
                        }
                    }
                }
                
                $project['bCount'] = $bCount;
                $project['uCount'] = $uCount;
                $project['cCount'] = $cCount;
                $project['feesCount'] = $feesCount;
                $projects[$project['project_id']] = $project;                
            }
            return $projects;
        }
        return false;
    }

    /**
     *  Return an array of repositories
     */
    public function getRepositoryList() {
    
        $query = "
            SELECT `repository`
            FROM `".PROJECTS."`
            ORDER BY `repository`";

        $repos = array();

        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            while ($project = mysql_fetch_assoc($result)) {
                $repos[] = $project['repository'];
            }
            return $repos;
        }
        return false;
    }

    /**
     * Build a project URL based on project_id
     */
    public function getProjectUrl($project_id) {
        $query = "SELECT * FROM `".PROJECTS."` WHERE `project_id`=". $project_id;
        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            $project = mysql_fetch_array($result);
            return SERVER_URL . $project['name'];
        } else {
            return false;
        }
    }

    public function getRepoUrl() {
        if ($this->getRepo_type() == 'git') {
            return $this->repository;
        } else {
            return SVN_BASE_URL . $this->repository;
        }
    }

    /**
     * Return project_id based on repository name
     */
    public function getIdFromRepo($repo) {
        $query = "SELECT `project_id` FROM `".PROJECTS."` WHERE `repository`='".$repo."'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $project_id = $row['project_id'];
            return $project_id;
        } else {
            return false;
        }
    }

    /**
      Determine if the given user_id owns the project
    */
    public function isOwner($user_id = false) {
        return $user_id == $this->getOwnerId();
    }
    public function isProjectCodeReviewer($user_id = false) {
        return array_key_exists($user_id, $this->getCodeReviewers());
    }
    
    /**
      Determine if the given user_id is a project runner
    */
    public function isProjectRunner($user_id = false) {
        return array_key_exists($user_id, $this->getRunners());
    }
    
    /**
     * new function for getting roles for the project <mikewasmike 15-JUN-2011>
     * @param int $project_id
     * @return array|null
    */
    public function getRoles($project_id, $where = ''){
        $query = "SELECT * FROM `".ROLES."` WHERE `project_id`={$project_id}";
        if (!empty($where)) {
            $query .= " AND ". $where;
        }
        $result_query = mysql_query($query);
            if ($result_query) {
            $temp_array = array();
            while ($row = mysql_fetch_assoc($result_query)) {
                    $temp_array[] = $row;
            }
            return $temp_array;
        } else {
            return null;
        }
    }
    
    /**
     * new function for adding roles in the project <mikewasmike 15-JUN-2011>
     * @param int $project_id
     * @param varchar $role_title
     * @param decimal $percentage
     * @param decimal $min_amount
     * @return int|null
     */
    public function addRole($project_id,$role_title,$percentage,$min_amount){
        $query = "INSERT INTO `".ROLES."` (id,`project_id`,`role_title`,`percentage`,`min_amount`)  VALUES(NULL,'$project_id','$role_title','$percentage','$min_amount')";
        return mysql_query($query) ? mysql_insert_id() : null;
    }
    
    /**
     * new function for editing roles in the project <mikewasmike 15-JUN-2011>
     * @param int $role_id
     * @param varchar $role_title
     * @param decimal $percentage
     * @param decimal $min_amount
     * @return 1|0
     */
    public function editRole($role_id,$role_title,$percentage,$min_amount){
        $query = "UPDATE `".ROLES."` SET `role_title`='$role_title',`percentage`='$percentage',`min_amount`='$min_amount' WHERE `id`={$role_id}";
        return mysql_query($query) ? 1 : 0;
    }
    
    /**
     * new function for deleting roles in the project <mikewasmike 15-JUN-2011>
     * @param int $role_id
     * @return 1|0
     */
    public function deleteRole($role_id){
        $query = "DELETE FROM `".ROLES."`  WHERE `id`={$role_id}";
        return mysql_query($query) ? 1 : 0;
    }  
    /**
     * Allows you to add a reviewer to the project
     * @param $codeReviewer_id
     * @return number
     */
    public function addCodeReviewer($codeReviewer_id) {
        $project_id = $this->getProjectId();
        $codeReviewer_id = (int) $codeReviewer_id;
        $query = "INSERT INTO `" . REL_PROJECT_CODE_REVIEWERS . "` (project_id, code_reviewer_id) " .
            "VALUES (" . $project_id . ", " . $codeReviewer_id . ")";
        return mysql_query($query) ? 1 : 0;
    }
    
    public function deleteCodeReviewer($codeReviewer_id) {
        $codeReviewer_id = (int) $codeReviewer_id;
        if ($codeReviewer_id == $this->getOwnerId()) {
            return false;
        }
        $project_id = $this->getProjectId();
        $query = "DELETE FROM `" . REL_PROJECT_CODE_REVIEWERS . "` " .
            "WHERE `project_id`={$project_id} AND `code_reviewer_id`={$codeReviewer_id}";
        return mysql_query($query) ? 1 : 0;
    }
    /**
     * Add Runner to Project
     */
    public function addRunner($runner_id) {
        $project_id = $this->getProjectId();
        $runner_id = (int) $runner_id;
        $query = 
            "INSERT INTO `" . PROJECT_RUNNERS . "` (project_id, runner_id) " .
            "VALUES (" . $project_id . ", " . $runner_id . ")";
        return mysql_query($query) ? 1 : 0;
    }
    
    /**
     * Remove Runner from Project
     */
    public function deleteRunner($runner_id) {
        $runner_id = (int) $runner_id;
        if ($runner_id == $this->getOwnerId()) {
            return false;
        }
        $project_id = $this->getProjectId();
        $query = 
            "DELETE FROM `" . PROJECT_RUNNERS . "` " .
            "WHERE `project_id`={$project_id} AND `runner_id`={$runner_id}";
        return mysql_query($query) ? 1 : 0;
    }
   
    /**
     * Get Runners Job Stats for Project
     */
    public function getRunners() {
        $query = 
            "SELECT DISTINCT u.id, u.nickname, (
                SELECT COUNT(DISTINCT(w.id))
                FROM " . WORKLIST . " w 
                LEFT JOIN " . PROJECT_RUNNERS . " p on w.project_id = p.project_id 
                WHERE w.runner_id = u.id 
                AND w.status IN('Bidding', 'Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done')  
                AND p.project_id = " . $this->getProjectId() . "
            ) totalJobCount 
            FROM " . USERS . " u 
            WHERE u.id IN (
                SELECT runner_id
                FROM rel_project_runners
                WHERE project_id = " . $this->getProjectId() . "
            ) 
            ORDER BY totalJobCount DESC";

        $result = mysql_query($query);
        if (is_resource($result) && mysql_num_rows($result) > 0) {
            while($row = mysql_fetch_assoc($result)) {
                $row['owner'] = ($row['id'] == $this->getOwnerId());
                $runners[$row['id']] = $row;
            }
            return $runners;
        } else {
            return false;
        }
    }
    
    /**
     * Get the Reviewers for current project
     * @return unknown|boolean
     */
    public function getCodeReviewers() {
       $query =
        "SELECT DISTINCT u.id, u.nickname, (
        SELECT COUNT(DISTINCT(w.id))
        FROM " . WORKLIST . " w
        LEFT JOIN " . REL_PROJECT_CODE_REVIEWERS . " p on w.project_id = p.project_id
        WHERE (w.runner_id = u.id OR w.creator_id = u.id OR w.mechanic_id = u.id)
        AND w.status IN('Bidding', 'Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done')
        AND p.project_id = " . $this->getProjectId() . "
        ) totalJobCount
        FROM " . USERS . " u
        WHERE u.id IN (
        SELECT code_reviewer_id
        FROM " . REL_PROJECT_CODE_REVIEWERS . "
        WHERE project_id = " . $this->getProjectId() . "
        )
        ORDER BY totalJobCount DESC";
       
        $result = mysql_query($query);
        
        if (is_resource($result) && mysql_num_rows($result) > 0) {
            while($row = mysql_fetch_assoc($result)) {
                $row['owner'] = ($row['id'] == $this->getOwnerId());
                $code_reviewers[$row['id']] = $row;
            }
            return $code_reviewers;
        } else {
            return array();
        }
    }
    /* 
     Get the list of allowed runners for Project
     */
    public static function getAllowedRunnerlist($project_id) {
        $runnerlist = array();
        $sql = 'SELECT `u`.*
        FROM `' . USERS . '` u 
        INNER JOIN `' . PROJECT_RUNNERS . '` `pr` ON (`u`.`id` = `pr`.`runner_id`) 
        WHERE `pr`.`project_id` = ' . $project_id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
          while ($result && ($row = mysql_fetch_assoc($result))) {
              $user = new User();
              $user->setId($row['id']);
              $user->setUsername($row['username']);
              $user->setNickname($row['nickname']);
              $runnerlist[] = $user;
          }
        }
        return ((!empty($runnerlist)) ? $runnerlist : false);
    }

    /* 
     Check if a Runner can run a specified project.
     */
    public static function isAllowedRunnerForProject($runner_id, $project_id) {
        $sql = 'SELECT `u`.*
        FROM `' . USERS . '` u 
        INNER JOIN `' . PROJECT_RUNNERS . '` `pr` ON (`u`.`id` = `pr`.`runner_id`) 
        WHERE `pr`.`project_id` = ' . $project_id . ' AND `u`.`id` = ' . $runner_id;
        
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
          return true;
        }
        return false;
    }

    /**
     * Get the last activity of the reviewer on the current project
     * @param $userId
     * @return boolean|string
     */
    public function getCodeReviewersLastActivity($userId) {
        $sql = "SELECT MAX(change_date) FROM " . STATUS_LOG . " s
            LEFT JOIN " . WORKLIST . " w ON s.worklist_id = w.id
            LEFT JOIN " . REL_PROJECT_CODE_REVIEWERS . " p on p.project_id = w.project_id
            WHERE s.user_id = " . $userId . " AND p.project_id = " . $this->getProjectId() . " ";
        $res = mysql_query($sql);
        if($res && $row = mysql_fetch_row($res)){
            $lastActivity = strtotime($row[0]);
            $rightNow = time();
            if($lastActivity == '') {
                return false;
            } else {
                return (formatableRelativeTime($lastActivity, 2) . " ago");
            }
        }
            return false;
    }
    
    public function getRunnersLastActivity($userId) {
        $sql = "SELECT MAX(change_date) FROM " . STATUS_LOG . " s 
                LEFT JOIN " . WORKLIST . " w ON s.worklist_id = w.id 
                LEFT JOIN " . PROJECT_RUNNERS . " p on p.project_id = w.project_id 
                WHERE s.user_id = '$userId' AND p.project_id = " . $this->getProjectId() . " ";
        $res = mysql_query($sql);
        if($res && $row = mysql_fetch_row($res)){
            $lastActivity = strtotime($row[0]);
            $rightNow = time();
            if($lastActivity == '') {
                return false;
            } else {
                return (formatableRelativeTime($lastActivity, 2) . " ago");
            }
        }
        return false;
    } 
    
    public function getFundName() {
        $query = "SELECT `name` FROM `" . FUNDS . "` WHERE `id` = {$this->getFundId()}";
        if ($result = mysql_query($query)) {
            $fund = mysql_fetch_assoc($result);
            return $fund['name'];
        } else {
            return false;
        }
    }
    
    public function getTotalJobs() {
        $query = "
            SELECT COUNT(w.id) jobCount
            FROM " . WORKLIST . " w 
            LEFT JOIN " . PROJECTS . " p ON w.project_id = p.project_id  
            WHERE
                w.status NOT IN ('Draft', 'Pass') AND
                p.project_id = " . $this->getProjectId();

        if($result = mysql_query($query)) {
            $count = mysql_fetch_assoc($result);
            return $count['jobCount'];
        } else {
            return 0;
        }
    }
    
    public function getAvgBidFee() {
        $query = "SELECT AVG(b.bid_amount) AS avgBidFeePerProject FROM " . BIDS . " b 
                  LEFT OUTER JOIN " . WORKLIST . " w on b.worklist_id = w.id 
                  LEFT OUTER JOIN " . PROJECTS . " p on w.project_id = p.project_id 
                  WHERE p.project_id = " . $this->getProjectId() . " AND b.accepted = 1";
        if($result = mysql_query($query)) {
            $count = mysql_fetch_assoc($result);
            return $count['avgBidFeePerProject'];
        } else {
            return 0;
        }
    }
    
    public function getAvgJobTime() {
        $query = "SELECT AVG(TIME_TO_SEC(TIMEDIFF(doneDate, workingDate))) as avgJobTime FROM 
                    (SELECT w.id, s.change_date AS doneDate,
                        ( SELECT MAX(`date`) AS workingDate FROM fees 
                          WHERE worklist_id = w.id AND `desc` = 'Accepted Bid') as workingDate 
                    FROM status_log s 
                    LEFT JOIN worklist w ON s.worklist_id = w.id 
                    LEFT JOIN projects p on p.project_id = w.project_id 
                    WHERE s.status = 'Done' AND p.project_id = " . $this->getProjectId() . ") AS x";
        if($result = mysql_query($query)) {
            $row = mysql_fetch_array($result);
            return ($row['avgJobTime'] > 0) ? relativeTime($row['avgJobTime'], false, true, false) : '';
        } else {
            return false;
        } 
    }
    
    public function getDevelopers() {
        $query = "
                SELECT DISTINCT u.id, u.nickname, 
                    (
                        SELECT COUNT(*) 
                        FROM " . WORKLIST . " w 
                            LEFT JOIN " . PROJECTS . " p on w.project_id = p.project_id 
                        WHERE ( w.mechanic_id = u.id OR w.creator_id = u.id) 
                            AND w.status IN ('Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done') 
                        AND p.project_id = "  . $this->getProjectId() . "
                    ) as totalJobCount,
                    (
                        SELECT SUM(F.amount) 
                        FROM " . FEES . " F 
                            LEFT OUTER JOIN " . WORKLIST . " w on F.worklist_id = w.id 
                            LEFT JOIN " . PROJECTS . " p on p.project_id = w.project_id 
                        WHERE (F.paid = 1 AND F.withdrawn = 0 AND F.expense = 0 AND F.user_id = u.id)
                            AND w.status IN ('Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done')                 
                            AND p.project_id = " . $this->getProjectId() . "
                    ) as totalEarnings
                    
                FROM " . BIDS . " b 
                    LEFT JOIN " . WORKLIST . " w ON b.worklist_id = w.id 
                    LEFT JOIN " . PROJECTS . " p ON p.project_id = w.project_id 
                    LEFT JOIN " . USERS . " u ON b.bidder_id = u.id 
                WHERE b.accepted = 1 
                    AND w.status IN ('Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done')
                    AND p.project_id = " . $this->getProjectId() . " 
                ORDER BY totalEarnings DESC";
        if($result = mysql_query($query)) {
            $developers = array(); 
            if(mysql_num_rows($result) > 0) {
                while($row = mysql_fetch_assoc($result)) {
                    $developers[$row['id']] = $row;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $developers;
    }
  
    public function getContributors() {
        $query = "
                SELECT DISTINCT u.id, u.nickname
                FROM " . FEES . " f 
                    LEFT JOIN " . WORKLIST . " w ON f.worklist_id = w.id 
                    LEFT JOIN " . PROJECTS . " p ON p.project_id = w.project_id 
                    LEFT JOIN " . USERS . " u ON f.user_id = u.id 
                WHERE f.paid = 1 
                    AND w.status IN ('Working', 'Functional', 'SvnHold', 'Review', 'Completed', 'Done')
                    AND p.project_id = " . $this->getProjectId() . "
                ORDER BY f.date DESC";
        $result = mysql_query($query);
        if ($result) {
            $contributors = array();
            if (mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    $contributors[] = $row;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $contributors;
    }

    public function getActiveJobs() {
        $query = "
                SELECT `id`, `summary`, `status`, `sandbox`
                FROM " . WORKLIST . " w
                WHERE w.status IN ('Bidding', 'Working', 'Functional', 'SvnHold', 'Review', 'Completed')
                    AND w.project_id = " . $this->getProjectId() . "
                ORDER BY w.created ASC";
        $result = mysql_query($query);
        if($result) {
            $jobs = array();
            if (mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    $jobs[] = $row;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return $jobs;
    }
    
    public function getDevelopersLastActivity($userId) {
        $sql = "SELECT MAX(change_date) FROM " . STATUS_LOG . " s 
                LEFT JOIN " . WORKLIST . " w ON s.worklist_id = w.id 
                LEFT JOIN " . PROJECTS . " p on p.project_id = w.project_id 
                WHERE s.user_id = '$userId' AND p.project_id = " . $this->getProjectId();
        $res = mysql_query($sql);
        if($res && $row = mysql_fetch_row($res)){
            $lastActivity = strtotime($row[0]);
            $rightNow = time();
            if($lastActivity == '') {
                return false;
            } else {
                if(($rightNow - $lastActivity) > 604800) { //if greater than a week i.e. 7*24*60*60 in seconds
                    return date('d-F-Y', $lastActivity);
                } else {
                    return (formatableRelativeTime($lastActivity, 2) . " ago");
                }
            }
        }
        return false;
    } 
    
    public function getPaymentStats() {
        $query = "SELECT u.id, u.nickname, f.worklist_id, f.amount, f.paid FROM " . FEES . " f
                  LEFT JOIN " . WORKLIST . " w ON f.worklist_id = w.id
                  LEFT JOIN " . USERS . " u ON f.user_id = u.id
                  WHERE w.status = 'Done' AND  w. project_id = " . $this->getProjectId() . "
                  AND f.withdrawn = 0 AND f. expense = 0
                  ORDER BY f.paid, f.worklist_id ASC";
        if ($result = mysql_query($query)) {
            $payments = array();
            if(mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                        $payments[] = $row;
                }
                return $payments;
            } else {
                return false;
            }
        } else {
            return false;
        }                
    }    

    /**
     * Return project_id based on project name
     */
    public function getIdFromName($name) {
        $query = "SELECT `project_id` FROM `" . PROJECTS . "`
            WHERE `name` = '" . mysql_real_escape_string($name) . "'";
        $result = mysql_query($query);
        if (mysql_num_rows($result)) {
            $row = mysql_fetch_assoc($result);
            $project_id = $row['project_id'];
            return $project_id;
        } else {
            return false;
        }
    }
    
    function getOwnerCompany() {
        if (!$this->getInternal()) {
            return $this->getName();
        } else {
            return "High Fidelity Inc.";
        }
    }

    public function makeApiRequest(
                    $path,
                    $method,
                    $token,
                    $params = array(),
                    $json = false) {
        // Define response defaults
        $error = false;
        $message = false;
        $data = false;
        $postString = '';
        $headers = array('Accept: application/json', 'User-Agent: Worklist.net');
        
        // Define variables required for API
        if ($path == 'login/oauth/access_token') {
            $apiURL = 'https://github.com/' . $path;
        } else {
            $apiURL = GITHUB_API_URL . $path;
        }

        $credentials = array(
            'client_id' => urlencode($this->github_id),
            'client_secret' => urlencode($this->github_secret)
        );
        
        $postArray = array_merge($params, $credentials);
        if ($json) {
            $postString = json_encode($params);
        } else {
            foreach ($postArray as $key => $value) {
                $postString .= $key . '=' . $value . '&';
            }
            rtrim($postString,'&');
        }
        
        // Initialize cURL
        $curl = curl_init();
        
        if ($method == 'POST') {
            if ($token && $path != 'login/oauth/access_token') {
                $headers[] = 'Authorization: token ' . $token;
            }
            curl_setopt($curl, CURLOPT_POST, count($postArray));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postString);
        } else if ($method == 'GET') {
            $apiURL .= '?' . $postString;
            if ($token && $path != 'login/oauth/access_token') {
                $apiURL .= (!empty($postString) ? '&' : '') . 'access_token=' . $token;
            }
        }
        
        //set the url, number of POST vars, POST data
        curl_setopt($curl, CURLOPT_URL, $apiURL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        try {
            $apiResponse = curl_exec($curl);
            if ($apiResponse && !curl_errno($curl)) {
                $error = false;
                $message = "API Call executed successfully";
                $data = json_decode($apiResponse, true);
            } elseif (curl_errno($curl)) {
                $error = true;
                $curlError = curl_error($curl);
                $message = "There was an error processing your request - ERR " . $curlError;
                $data = array(
                    'error' => $curlError);
            }
        } catch(Exception $ex) {
            $error = true;
            $message = $ex;
            $data = array(
                'error' => $ex
            );
        };
        
        return array(
            'error' => $error,
            'message' => $message,
            'data' => $data);
    }
    
    public function extractOwnerAndNameFromRepoURL() {
        $repoDetails = array();
        // Get rid of protocol, domain and .git extension
        $removeFromString = array(
            'http://',
            'https://',
            'github.com',
            'www.github.com',
            '.git');
        $cleanedRepoURL = str_replace($removeFromString, '', $this->getRepository());
        $explodedRepoURL = explode('/', $cleanedRepoURL);
        $repoDetails['owner'] = $explodedRepoURL[1];
        $repoDetails['name'] = $explodedRepoURL[2];
        return $repoDetails;
    }
    
    public function pull_request($payload) {
        $headLabel = $payload->pull_request->head->label;
        $labelComponents = explode(':', $headLabel);
        $jobNumber = trim($labelComponents[1]);
        // Try to extract job number from head repository label
        if (preg_match('/^[0-9]{3,}$/', $labelComponents[1])) {
            $workItem = new WorkItem();
            // We have what looks like a workitem number, see if it exists
            // and if it does, we set job to completed and post comment to
            // journal
            if ($workItem->idExists($jobNumber)
                && $payload->pull_request->state == 'closed') {
                
                $workItem->loadById($jobNumber);
                $pullRequestNumber = $payload->pull_request->number;
                $pullRequestURL = $payload->pull_request->html_url;
                $pullRequestBase = $payload->pull_request->base->label;
                $pullRequestStatus = $payload->pull_request->merged == 'true' 
                    ? "closed and merged"
                    : "closed but not merged";
                $message = 
                    "#{$jobNumber} - Pull request {$pullRequestNumber}\n\n" .
                    "({$pullRequestURL}) has been {$pullRequestStatus} into {$pullRequestBase}";

                sendJournalNotification($message);
                
                if ($payload->pull_request->merged == 'true') {
                    $journal_message = "Job #" . $jobNumber . ' has been automatically set to *Completed*';
                    sendJournalNotification($journal_message);
                    $workItem->setStatus('Completed');
                    $workItem->addFeesToCompletedJob(true);
                    $workItem->save();
                }
            }
        }
    }
}
