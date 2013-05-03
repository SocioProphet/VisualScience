<?php 

require_once 'visualscience.searchtable.class.php';
require_once 'visualscience.config.class.php';

function visualscience_patterns ($data = NULL)  {
	$action['visualscience_config'] = array(
		PATTERNS_INFO   => t('Modify Configuration for VisualScience'),
		PATTERNS_CREATE => array('visualscience_insert_config'),
		PATTERNS_MODIFY => array('visualscience_modify_config'),
		PATTERNS_EXPORT => array(
			PATTERNS_EXPORT_ALL => 'visualscience_export_config',
			),
		);

	return $action;
}

function visualscience_patterns_validate ($action, $tag, &$data) {
	$config = new Config;
	$field = $data;
	$uncompleteField = $config->checkCompletefield($field);
	$result = [];
	$status = PATTERNS_SUCCESS;
	$msg = 'An error occured in your file.';
	if ($action == PATTERNS_CREATE) {
		if (!$config->fieldExistsInDB($field)) {
			if (!$uncompleteField) {
				if (!($wrongValueType = $config->checkCorrectValueTypes($field))) {
					$msg = '';
				}
				else {
					$status = PATTERNS_ERR;
					$msg = t('The field '.$wrongValueType.' has a wrong value type for '.$field['name'].'.');
				}
			}
			else {
				$status = PATTERNS_ERR;
				$msg = t('The field '.$uncompleteField.' is not defined for '.$field['name'].'.');
			}
		}
		else {
			$msg = t('The field already exists in the database.');
			$result[] = array(
				PATTERNS_WARNING_ELEMENT_UNDEFINED => t('The field '.$field['name'].' already exists in the database.')
				);
		}
	}

	if ($action == PATTERNS_MODIFY) {
		if ($config->fieldExistsInDB($field)) {
			if (!$uncompleteField) {
				if (!($wrongValueType = $config->checkCorrectValueTypes($field))) {
					$msg = '';
				}
				else {
					$status = PATTERNS_ERR;
					$msg = t('The field '.$wrongValueType.' has a wrong value type for '.$field['name'].'.');
				}
			}
			else {
				$status = PATTERNS_ERR;
				$msg = t('The field '.$uncompleteField.' is not defined for '.$field['name'].'.');
			}
		}
		else {
			$msg = t('The field does not already exist in the database.');
			$result[] = array(
				PATTERNS_WARNING_ELEMENT_UNDEFINED => t('The field '.$field['name'].' does not already exist in the database.')
				);
		}
	}
	return patterns_results($status, $msg, $result);
}

function visualscience_insert_config ($form_id, $form_state) {
	$config = new Config;
	$field = $form_state['values'];
	$config->insertPatternConfig($field);
	
}

function visualscience_modify_config ($form_id, $form_state) {
	$config = new Config;
	$field = $form_state['values'];
	$config->modifyPatternConfig($field);	
}

function visualscience_export_config ($args = NULL, &$result = NULL) {
	$actions = array();
	$action_type = PATTERNS_MODIFY; // pre-init 
	
	if (isset($args['type']) && $args['type'] == PATTERNS_CREATE) {
		$action_type = PATTERNS_CREATE;
	}

	$search = new Search;
	$fields =  $search->getPatternConfiguration();
	foreach ($fields as $field) {
		$action = array($action_type => array(
			'tag' => 'visualscience_config', 
			'name' => $field['name'], 
			'mini' => $field['mini'], 
			'full' => $field['full'], 
			'first' => $field['first'], 
			'last' => $field['last']
			));

		array_push($actions, $action);
	}

	return $actions;
}