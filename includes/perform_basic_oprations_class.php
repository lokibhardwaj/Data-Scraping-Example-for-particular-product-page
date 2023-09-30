<?php


//$table_name = 'test';/***** Name of the database table *******/
//$opration = 'SELECT';/*****Opration to be performed on table e.g. INSERT, UPDATE, DELETE, SELECT *******/

//$field_name_and_value = $_POST;/********* To handle post when posted input field names are same as in the field names in the table                                otherwise array can be created in which array key should be the field name in database table and 								                                value shold be the value you want to insert in that particular field. ************************/

//$select_fields = '';/********* You can give names as $select_fields = 'fiels1, field1, field3....(name of fiels seprated with                    comma)'; to select specific fields from table ********/

//$conditions = "where id = '1'";/********* Here you can use condition as 'where user_id = 1 AND login_time = 2' etc *******/
class perform_basic_oprations
{
	/********** Function to INSERT, UPDATE, DELETE, SELECT from database ****************/
	function perform_opration($connection, $table_name, $opration, $field_name_and_value, $select_fields, $conditions)
	{
		/******** To INSERT record *********************/
		if($opration == 'INSERT' || $opration == 'insert')
		{
			$count = 0;
			foreach($field_name_and_value  as $key => $value)
			{
					$field_name[$count] = $key;
					$field_value[$count++] = $value;
			}
			$field_names = implode(',' , $field_name);
			$field_values = implode("','", $field_value);
			$field_values = "('".$field_values."')";
			//print ("$opration INTO $table_name ($field_names) values $field_values");
			$insert = mysqli_query($connection, "$opration INTO $table_name ($field_names) values $field_values");
			if(mysqli_insert_id($connection))
			{
				$result = mysqli_insert_id($connection);
			}
			else
			{
				$result = 0;
			}
		}
		/********* To update record **********/
		else if($opration == 'UPDATE' || $opration == 'update')
		{

			$check = 0;
			$count = 0;
			foreach($field_name_and_value  as $key => $value)
			{
				if($check == 0)
				{
					$update_field_and_value[$count++] = $key." = '".$value."'";
					$check =1;
				}
				else
				{
					$update_field_and_value[$count++] = " ".$key." = '".$value."' ";
				}
			}
			$update_fields_and_values = implode(',' ,$update_field_and_value);
			//print ("$opration $table_name SET $update_fields_and_values $conditions");print "<br />";
			$update = mysqli_query($connection, "$opration $table_name SET $update_fields_and_values $conditions");
			if(mysqli_affected_rows($connection) > 0)
			{
				$result = 1;
			}
			else
			{
				$result = 0;
			}
		}
		/*********** To delet records using key ***********/
		else if($opration == 'DELETE' || $opration == 'delete' && !empty($conditions))
		{
			$delete = mysqli_query($connection, "DELETE from $table_name $conditions");
			if(mysqli_affected_rows($connection) > 0)
			{
				$result = 1;
			}
			else
			{
				$result = 0;
			}
		}
		/********* To select records whith or whithout condition ********************/
		else if($opration == 'SELECT' || $opration == 'select')
		{
			if(empty($select_fields))
			{
				//print ("SELECT * from $table_name $conditions");
				$select = mysqli_query($connection, "SELECT * from $table_name $conditions");
			}
			else
			{
				//print ("SELECT $select_fields from $table_name $conditions");
				$select = mysqli_query($connection, "SELECT $select_fields from $table_name $conditions");
			}
			if(mysqli_num_rows($select)>0)
			{
				$count =0;
				while($rows = mysqli_fetch_assoc($select))
				{
					$result[$count++] = $rows;
				}
			}
		}
	return $result;
	}



	/**************** Function to check if the record already exist or not ***********************/

	function record_exist($connection, $table_name, $check_condition)
	{


		//print ("SELECT * from $table_name $conditions");
		$select = mysqli_query($connection, "SELECT * from $table_name $check_condition");
		if(mysqli_num_rows($select)>0)
		{
			$result = 1;
		}
		else
		{
			$result = 0;
		}

	return $result;
	}
}


?>
