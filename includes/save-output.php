<?php
/*print "<pre>";
print_r($_SESSION['output_arr']);
print "</pre>";*/
if(isset($_SESSION['output_arr'])){
    $output_arr = $_SESSION['output_arr'];
    include("connection.inc");
    include("perform_basic_oprations_class.php");
    $success_counter = 0;
    $error_counter = 0;
    $already_exist_counter = 0;
    $obj = new perform_basic_oprations();
    foreach($output_arr as $keymain => $field_name_and_value){
        $check_condition = 'where reg="'.$field_name_and_value['reg'].'"';
        $result = $obj->record_exist($connection, $table_name='data', $check_condition);
        if($result == 0){
            $result = $obj->perform_opration($connection, $table_name='data', $opration='insert', $field_name_and_value, $select_fields='', $conditions='');
            if($result > 0){
               $success_counter++;
            }else{
                $error_counter++;
            }
        }else{
            $already_exist_counter++;
        }

    }
    if($success_counter > 0){
        print $success_counter." record(s) added successfully!";
    }elseif($error_counter > 0){
        print $error_counter." error(s) during record insertion!";
    }elseif($already_exist_counter > 0){
        print $already_exist_counter." record(s) already exist!";
    }

}
?>