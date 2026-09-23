<?php
$fullPage = explode('/',$_SERVER['REQUEST_URI']);
$fullPageName = isset($fullPage[count($fullPage)-1]) ? $fullPage[count($fullPage)-1] : '';
$req = explode('?',$fullPageName);
$request = isset($req[1]) ? '?'.$req[1] : '';

$pg = explode('/',$_SERVER['PHP_SELF']);
$pgName = isset($pg[count($pg)-1]) ? $pg[count($pg)-1] : '';
#print_r($pg);
?>
                    <select name="selTopic" id="selTopic" onChange="window.location=this.value+'<?php echo $request?>'" style="width:300px;">
                        <option value="employee_edit.php" <?php if($pgName=="employee_edit.php"){echo 'selected="selected"';}?>>PERSONAL INFORMATION</option>
                        <option value="employee_add_family.php" <?php if($pgName=="employee_add_family.php"){echo 'selected="selected"';}?>>FAMILY BACKGROUND</option>
                        <option value="employee_add_educ.php" <?php if($pgName=="employee_add_educ.php"){echo 'selected="selected"';}?>>EDUCATIONAL BACKGROUND</option>
                        <option value="employee_add_prc.php" <?php if($pgName=="employee_add_prc.php"){echo 'selected="selected"';}?>>PRC</option>
                        <option value="employee_add_work_exp.php" <?php if($pgName=="employee_add_work_exp.php"){echo 'selected="selected"';}?>>WORK EXPERIENCE</option>
                        <option value="employee_add_training.php" <?php if($pgName=="employee_add_training.php"){echo 'selected="selected"';}?>>TRAINING</option>
                        <option value="employee_add_license.php" <?php if($pgName=="employee_add_license.php"){echo 'selected="selected"';}?>>LICENSES</option>
                        <option value="employee_add_spec_award.php" <?php if($pgName=="employee_add_spec_award.php"){echo 'selected="selected"';}?>>AWARD</option>
                        <option value="employee_add_seminar.php" <?php if($pgName=="employee_add_seminar.php"){echo 'selected="selected"';}?>>SEMINAR</option>
                        <option value="employee_add_references.php" <?php if($pgName=="employee_add_references.php"){echo 'selected="selected"';}?>>REFERENCES</option>
                        <option value="employee_add_duty.php" <?php if($pgName=="employee_add_duty.php"){echo 'selected="selected"';}?>>DUTY HOURS ASSIGNMENT</option>
                        <option value="employee_add_ctc.php" <?php if($pgName=="employee_add_ctc.php"){echo 'selected="selected"';}?>>CTC</option>
                        <option value="employee_add_work_stat.php" <?php if($pgName=="employee_add_work_stat.php"){echo 'selected="selected"';}?>>WORK STATUS</option>
                        <option value="employee_manage_salary.php" <?php if($pgName=="employee_manage_salary.php"){echo 'selected="selected"';}?>>SALARY</option>
                        <option value="employee_leave.php" <?php if($pgName=="employee_leave.php"){echo 'selected="selected"';}?>>LEAVES</option>
                        <option value="employee_detail.php">VIEW DETAILS</option>
                    </select>