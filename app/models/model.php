<?php 

namespace App\Models;


class Model
{
	var $regex = array('email' =>  "/[a-szA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/",
		'data' => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/');
	public $tableName;
	
	var $validations = array();
	var $validation_errors = array();
	
	public $translated_fields;
	public $primaryKey = 'id';
	
	public $force_translation = false;
	public $sql_esconder = '';
	
	public $filters = array();
	
	public $maxfilesize = 1000000;
	public $log;
	
	function __construct() {
		
		$this->f3 = \Base::instance();
		$this->db = new \App\Plugins\db();
		$this->log = \Base::instance()->get('log');
	}
	
	public function validate(){
		
		$valid = true;
		$this->validation_errors = array();
		$failed_validated_fields = array();
		$failed_fields = array();
		foreach($this->validations as $validations_list){
			
			foreach($validations_list as $field => $validations){
				
				foreach($validations as $type_v => $val_v){
					$valid_field = true;
					
					if(isset($validations['name']))
					{
						$field_name = $validations['name'];
					}else{
						$field_name = $field;
					}
					
					
					if(isset($failed_validated_fields[$field]))
					{
						continue;
					}
					
					if($type_v == 'required')
					{
						if(strlen($val_v) > 1){
							$message = $val_v;
						}else{
							$message = $this->getTranslation("validation_required");
						}
						if($this->f3->exists('POST.'.$field)){
							$val = $this->f3->get('POST.'.$field);
							if(empty($val))
							{
								$msg = $message;
								$valid_field = false;
							}
						}else{
							$msg = $message;
							$valid_field = false;
						}
					}
					if($type_v == 'minlength')
					{
						
						if($this->f3->exists('POST.'.$field)){
							$val = $this->f3->get('POST.'.$field);
							
							if(strlen($val) < $val_v)
							{
								$msg = $this->getTranslation("validation_minlength")." (".$val_v.")";
								
								$valid_field = false;
							}
						}
					}
					if($type_v == 'minwords')
					{
						
						if($this->f3->exists('POST.'.$field)){
							$val = $this->f3->get('POST.'.$field);
							
							if(str_word_count($val) < $val_v)
							{
								$msg = $this->getTranslation("validation_minwords")." (".str_word_count($val).",".$val_v.")";
								
								$valid_field = false;
							}
						}
					}
					
					if($type_v == 'maxwords')
					{
						
						if($this->f3->exists('POST.'.$field)){
							$val = $this->f3->get('POST.'.$field);
							
							if(str_word_count($val) > $val_v)
							{
								$msg = $this->getTranslation("validation_maxwords")." (".str_word_count($val).",".$val_v.")";
								
								$valid_field = false;
							}
						}
					}
					
					if($type_v == 'image')
					{
						
						if($this->f3->exists('FILES.'.$field)){
							$file = $this->f3->get('FILES.'.$field);
							if(!empty($file['name'])){
								if(!in_array($file['type'],array("image/png","image/jpg","image/jpeg")))
								{
									$msg = $this->getTranslation("validation_imagetype")." (".$file['type'].")";
									
									$valid_field = false;
								}
							}
						}
						
						
					}
					if($type_v == 'filesize')
					{
						if($this->f3->exists('FILES.'.$field)){
							$file = $this->f3->get('FILES.'.$field);
							
							if($file['size'] > $val_v)
							{
								$msg = $this->getTranslation("validation_filesize")." (".$file['size'].", ".$val_v.")";
								
								$valid_field = false;
							}
							
						}
						
						
					}
					
					if($type_v == 'confirm')
					{
						
						if($this->f3->exists('POST.'.$field)){
							$val = $this->f3->get('POST.'.$field);
							$val_confirm = $this->f3->get('POST.'.$val_v);
							
							if($val != $val_confirm)
							{
								$msg = $this->getTranslation("validation_mismatch");
								
								$valid_field = false;
							}
						}
					}
					
					if($type_v == 'regex')
					{
						
						$val = $this->f3->get('POST.'.$field);
						
						if($val_v == 'data' && !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $val))
						{
							$msg = $this->getTranslation("validation_data");
							$valid_field = false;
						}
						if($val_v != 'data' && !preg_match($val_v, $val))
						{
							$msg = $this->getTranslation("validation_regex");
							$valid_field = false;
						}
					}
					if($type_v == 'email'){
						$val = $this->f3->get('POST.'.$field);
						if(!filter_var($val, FILTER_VALIDATE_EMAIL))
						{
							$msg = $this->getTranslation("validation_email");
							$valid_field = false;
						}
					}
					if($type_v == 'url'){
						$val = $this->f3->get('POST.'.$field);
						if(!filter_var($val, FILTER_VALIDATE_URL))
						{
							$msg = $this->getTranslation("validation_site");
							$valid_field = false;
						}
					}
					
					if(!$valid_field)
					{
						$valid = false;
						$failed_validated_fields[$field] = 1;
						
						$this->validation_errors[] = array('msg' => $msg,'field' => $field_name);
						$failed_fields[$field] = $msg;
						
						
					}
				}
				
				
				
			}
		}
		
		if(!empty($this->validation_errors)){
			$this->f3->set('SESSION.validate.errors',$this->validation_errors);
			$this->f3->set('SESSION.validate.fields',$failed_fields);
		}
		// echo "<pre>";
		// print_r($this->validation_errors);
		// echo "</pre>";
		return $valid;
	}
	
	
	
	function saveFile($inputname,$fdst='jpg', $w=900, $h=900)
	{
		
		if(empty($this->destination_dir)){
			$this->destination_dir = $this->f3->get('UPLOAD_DIR');
		}
		if($this->f3->exists('FILES.'.$inputname)){
			$file = $this->f3->get('FILES.'.$inputname);
			if(!empty($file['name'])){
				$nome_servidor = $this->destination_dir.uniqid().$inputname;
				if(!move_uploaded_file($file['tmp_name'], $nome_servidor)){
					return false;
				}
				fimage($nome_servidor,$nome_servidor,$fdst,$h,$w);
				$this->f3->set('POST.'.$inputname,$nome_servidor.'.'.$fdst);
				$this->log('saved file: '.$nome_servidor.'.'.$fdst);
				return true;
			}
		}
		
		return false;
	}
	
	function getRows($conds = '',$select = '*',$showQuery = false){
		
		$rows = $this->db->getRows($this->tableName,$conds,$select,$showQuery);
		
		if(!empty($this->hasMany)){
		    foreach($this->hasMany as $has){
                $modelName = $has["Model"];
                $referenceKey = $has["referenceKey"];
                                
                if(isset($has["foreignKey"])){
                    $foreignKey = $has["foreignKey"];    
                }else{
                    $foreignKey = $has["referenceKey"];
                }

                $namespace = "\\App\\Models\\$modelName";
                $model = new $namespace;
                
                foreach($rows as $key => $row){
                    if(!empty($row[$referenceKey])){
                        $tipo_estado_id = $row[$referenceKey];
                        
                        //echo "tipo_estado_id: ".$row['id']." $tipo_estado_id $key<br/>";
                        $conds = "WHERE $foreignKey = $tipo_estado_id";
                        //echo $conds;
                        $results = $model->getRows($conds);
                        //print_r($results);
                        if(!empty($results)){
                            $rows[$key][$modelName] = $results;
                        }
                        //die("OI?");
                    }
                }
            }
        }
        
        if(!empty($this->hasOne)){
		    foreach($this->hasOne as $has){
		    	
		    	$modelName = $has["Model"];
                $namespace = "\\App\\Models\\$modelName";
                $model = new $namespace;
                
		    	
                $referenceKey = $has["referenceKey"];
                                
                if(isset($has["foreignKey"])){
                    $foreignKey = $has["foreignKey"];    
                }else{
                    $foreignKey = $model->primaryKey;
                }

                
                
                foreach($rows as $key => $row){
                    if(!empty($row[$referenceKey])){
                        $val = $row[$referenceKey];
                        
                        $conds = "WHERE $foreignKey = $val";
                        // echo $conds;
                        $results = $model->getRow($conds);
                        // print_r($results);
                        if(!empty($results)){
                            $rows[$key][$modelName] = $results;
                        }
                        // die('oi');
                    }
                }
            }
        }
        
		if(!empty($this->force_translation)){
			$language_set = $this->force_translation;
		}else{
			$language_set = $this->f3->get('lang_set');
		}
		
		$default_lang = $this->f3->get('LANG');
		
		if(is_array($this->translated_fields) && !empty($language_set)){
			
			foreach($rows as $key => $row){
				foreach($row as $_key => $_values){
					if(in_array($_key,$this->translated_fields)){
						
						$q = "SELECT field_value FROM i18n_translations 
						WHERE 
						table_name like '".$this->tableName."' AND
						field_id = ".$rows[$key][$this->primaryKey]." AND 
						field_name like '".$_key."' AND
						language like '".$language_set."' ";
						$res = $this->db->exec($q);
						
						if(!empty($res[0]['field_value'])){
							$rows[$key][$_key] = $res[0]['field_value']."   ";
						}else{
							$q = "SELECT field_value FROM i18n_translations 
							WHERE 
							table_name like '".$this->tableName."' AND
							field_id = ".$rows[$key][$this->primaryKey]." AND 
							field_name like '".$_key."' AND
							language like '".$default_lang."' ";
							$res = $this->db->exec($q);
							if(!empty($res[0]['field_value'])){
								$rows[$key][$_key] = $res[0]['field_value']."   ";
							}
							
							
						}
						
					}
				}
			}
		}
		
		return $rows;
		
	}
	
	public function getRow($conds = '',$select = '*',$showQuery = false){
		$res = $this->getRows($conds.' LIMIT 1',$select,$showQuery);
		if(isset($res[0]))
		{
			return $res[0];
		}
		return array();
	}
	
	public function edit(){
		$i18n = $this->f3->get('POST.i18n');
		if(strlen($i18n) > 1){
			
			foreach($this->translated_fields as $field){
				if($this->f3->exists('POST.'.$field)){
					$res = $this->db->getRow('i18n_translations',"WHERE 
						table_name like '".$this->tableName."' AND
						field_id like '".$this->f3->get('POST.'.$this->primaryKey)."' AND
						field_name like '".$field."' AND
						language like '".$this->f3->get('POST.i18n')."' ");
				 	if(empty($res[$this->primaryKey])){
				 		
				 		$this->db->insert(array('table_name' => "'".$this->tableName."'",
				 			'field_id' => "'".$this->f3->get('POST.'.$this->primaryKey)."'",
				 			'field_name' => "'".$field."'",
				 			'field_value' => "'".$this->f3->get('POST.'.$field)."'",
				 			'language' => "'".$this->f3->get('POST.i18n')."'",
				 			),'i18n_translations');
				 		
				 	}else{
				 		$this->db->update('i18n_translations',$this->primaryKey.' = '.$res['id'],array('field_value' => "'".$this->f3->get('POST.'.$field)."'"));
				 	}
				 	
				 	if($i18n != 'en'){
				 		$this->f3->clear('POST.'.$field);
				 	}
				}
			}
		}
			
		return $this->db->edit($this->f3->get('POST.'.$this->primaryKey),$this->tableName,$this->primaryKey);
	}
	public function getValue($field,$conds, $showQuery = false){
		$res = $this->getRows($conds.' LIMIT 1',$field,$showQuery);
		if(isset($res[0][$field]))
		{
			return $res[0][$field];
		}
		return array();
	}
	
	public function delete($id){
		return $this->db->delete($id,$this->tableName);
	}
	
	
	public function save(){
		
		if(!$this->beforeSave()){
			return false;
		}
		
		if($this->f3->exists('POST.'.$this->primaryKey)){
			$key = $this->f3->get('POST.'.$this->primaryKey);
		}
		
		if(!empty($key)){
			if(!$this->edit()){
				return false;
			}
		}else{
			
			if(!$this->db->add($this->tableName)){
				return false;
			}
		}
		if(!$this->afterSave()){
			return false;
		}
		
		return true;
		
	}
	
	public function beforeSave(){
		if($this->alwaysValidate && !$this->validate()){
			$this->error($this->getTranslation('validation_error'));
			return false;
		}
		if(!$this->f3->exists('POST.id')){
			$this->f3->set('POST.created',date('Y-m-d H:i:s'));
		}
		
		$this->f3->set('POST.modified',date('Y-m-d H:i:s'));
		
		
		
		return true;
		
	}
	
	public function afterSave()
	{
		$this->msg($this->getTranslation('submission_ok'));
		return true;
	}
	
	public function down($id){
		
		
		$item = $this->db->getRow($this->tableName,"WHERE id = $id");
		$nova_ordem = $item['ordem']+1;
		
		$this->db->update($this->tableName,"ordem = ".$nova_ordem, array("ordem" => "ordem-1"));
		
		$this->db->update($this->tableName,"id = $id", array("ordem" => $nova_ordem));
		return true;
	}
	
	public function up($id){
		$item = $this->db->getRow($this->tableName,"WHERE id = $id");
		$nova_ordem = $item['ordem']-1;
		
		$this->db->update($this->tableName,"ordem = ".$nova_ordem, array("ordem" => "ordem+1"));
		
		$this->db->update($this->tableName,"id = $id", array("ordem" => $nova_ordem));
		return true;
	}
	
	public function all($conds = '',$select = '*',$showQuery = false){
		return $this->getRows($conds,$select,$showQuery);
	}
	
	public function paginate($page = 1, $page_views = 5, $order_field = 'created', $order_order = 'DESC',$conds = '',$pageCountSelect = 'id', $select = '*',$showQuery = false)
	{
		
		$order_by = "";
		$limit = "";
		$tableName = $this->tableName;
		if(!empty($order_field))
		{
			if(empty($order_order))
			{
				$order_order = 'desc';
			}
			$order_by = "order by $order_field $order_order";
		}
		
		if(empty($page_views))
		{
			$page_views = 5;
		}
		if(empty($page))
		{
			$page = 1;
		}
		$limit =  "LIMIT ".($page-1) * $page_views.",".$page_views;
		
		$sql = "select CEIL(count($pageCountSelect)/$page_views) as nr_paginas from $tableName $conds $order_by;";
		if($showQuery){
			
		}
		
		$total_pages = $this->db->exec($sql);
		$total_pages = $total_pages[0]['nr_paginas'];
		
		$sql = "SELECT $select FROM $tableName $conds $order_by $limit";
		if($showQuery){
			echo $sql;
		}
		
		$conds = "$conds $order_by $limit";
		
		$results =  $this->getRows($conds,$select);
		return array("query" => $sql, "page" => $page, "order_field" => $order_field, "order_order" => $order_order,"total_pages" => $total_pages, "pageview" => $page_views,"results" => $results);
	}
	
	public function getById($id){
		
		return $this->getRow("WHERE ".$this->tableName.".".$this->primaryKey." = $id ".$this->sql_esconder);
	}
	
	public function update($where, $data,$showQuery = false){ 
		/*foreach($data as $field => $value){
		if(in_array($field,$this->translated_fields)){
		$res = $this->db->getRow('i18n_translations',"WHERE 
		table_name like '".$this->tableName."' AND
		field_id like '".$this->f3->get('POST.'.$this->primaryKey)."' AND
		field_name like '".$field."' AND
		language like '".$this->f3->get('POST.i18n')."'");
		if(empty($res['id'])){
		
		$this->db->insert(array('table_name' => "'".$this->tableName."'",
		'field_id' => "'".$this->f3->get('POST.'.$this->primaryKey)."'",
		'field_name' => "'".$field."'",
		'field_value' => "'".$this->f3->get('POST.'.$field)."'",
		'language' => "'".$this->f3->get('POST.i18n')."'",
		),'i18n_translations');
		
		}else{
		$this->db->update('i18n_translations','id = '.$res['id'],array('field_value' => "'".$this->f3->get('POST.'.$field)."'"));
		}
		die("THERE IS A FIELD TO TRANSLATE... update() ... model...");
		}
		}*/
		return $this->db->update($this->tableName,$where, $data,$showQuery);
	}
	
	public function getRowValue($fieldName,$conds = '',$showQuery=false) {
		
		$res = $this->getRow($conds,$fieldName,$showQuery);
		
		if(!empty($res[$fieldName]))
		{
			return $res[$fieldName];
		}else{
			return '';
		}
	}
	
	public function getCombined($value,$key = 'id',$conds = '',$select = '*',$showQuery = false){
		$res = $this->getRows($conds,$select,$showQuery);
		
		$resCombined = array();
		foreach($res as $result)
		{
			if(isset($result[$key]) && isset($result[$value])){
				$resCombined[$result[$key]] = $result[$value];
			}
		}
		return $resCombined;
	}
	
	public function getFilters(){
		$conds = "WHERE 1";
		foreach($this->filters as $filter){
			if($this->f3->exists("GET.$filter")){
				$conds .= " AND LOWER($filter) like '%".strtolower($this->f3->get("GET.$filter"))."%'";
			}
		}
		return $conds;
	}
	
	public function getTranslation($name)
	{
		return \App\Controllers\Translation::getTranslation($this->f3->get('lang_set'),$name);
	}
	
	function slugify($string) {
		// Make the whole string lowercase
		$slug = strtolower($string);
		// Replace utf-8 characters with 7-bit ASCII equivelants
		// $slug = iconv("utf-8", "ascii//TRANSLIT", $slug);
		// Replace any number of non-alphanumeric characters with hyphens
		$slug = preg_replace("/[^a-z0-9-]+/", "-", $slug);
		// Remove any hyphens from the beginning & end of the string
		return trim($slug, "-");
	}
	
	public function log($msg)
	{
		$msg = date('Y-m-d h:i').":: $msg";
		$this->log->write($msg);
	}
	
	function msg($msg){
		$msgs = $this->f3->get('SESSION.msg');
		$msgs[] = $msg;
		
		$this->f3->set('SESSION.msg',$msgs);
		
		$this->log($msg);
	}
	function error($msg){
		$msgs = $this->f3->get('SESSION.error_msg');
		$msgs[] = $msg;
		$this->f3->set('SESSION.error_msg',$msgs);
		
		$this->log("error: ".$msg);
	}
	
	function lang(){
		return $this->f3->get('lang_set');
	}
}
