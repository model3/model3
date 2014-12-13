<?php

class Model3_Scaffold_Model
{

    protected $_model;
    protected $_columns;
    protected $_columnTypes = array(
        'integer' => 'text',
        'decimal' => 'text',
        'float' => 'text',
        'string' => 'text',
        'varchar' => 'text',
        'boolean' => 'checkbox',
        'timestamp' => 'text',
        'time' => 'text',
        'date' => 'text',
        'enum' => 'select'
    );
    protected $_defaultValidators = array(
        'integer' => 'int',
        'float' => 'float',
        'double' => 'float'
    );
    protected $_validators = array();
    protected $_ignoreColumns = array();
    protected $_fieldInputs = array();
    protected $_fieldLabels = array();
    protected $_fieldOrder = array();
    protected $_relations = array();
    protected $_enums = array();
    protected $_doctrineTable;
    protected $_currentId = null;
    protected $_view = null;
    protected $_editLink = true;
    protected $_deleteLink = true;
    protected $_editController = null;
    protected $_editAction = 'edit';
    protected $_deleteController = null;
    protected $_deleteAction = 'delete';
    protected $_filterField = null;
    protected $_filterValue = null;
    protected $_externalActions = array();
    private $_cache = array();
    protected $_formElementPrepend = '';
    protected $_formElementAppend = '';
    protected $_formElementInputClass = '';

    public function __construct()
    {
        $this->_doctrineTable = Doctrine::getTable($this->_model);
        $this->_columns = $this->_doctrineTable->getColumns();
    }

    public function setCurrentId($id)
    {
        $this->_currentId = $id;
    }

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function setEditController($editController)
    {
        $this->_editController = $editController;
    }

    public function setEditAction($editAction)
    {
        $this->_editAction = $editAction;
    }

    public function setDeleteController($deleteController)
    {
        $this->_deleteController = $deleteController;
    }

    public function setDeleteAction($deleteAction)
    {
        $this->_deleteAction = $deleteAction;
    }

    public function addEnum($field, $options)
    {
        $this->_enums[$field] = $options;
    }

    public function addIgnoreColumn($field)
    {
        if (!in_array($field, $this->_ignoreColumns))
            $this->_ignoreColumns[] = $field;
    }

    public function deleteIgnoreColumn($field)
    {
        $key = array_search($field, $this->_ignoreColumns);
        if ($key !== null)
        {
            unset($this->_ignoreColumns[$key]);
        }
    }

    public function getFilterField()
    {
        return $this->_filterField;
    }

    public function getFilterValue()
    {
        return $this->_filterValue;
    }

    public function setFilterField($filterField)
    {
        $this->_filterField = $filterField;
    }

    public function setFilterValue($filterValue)
    {
        $this->_filterValue = $filterValue;
    }

    public function setFormElementPrepend($formElementPrepend)
    {
        $this->_formElementPrepend = $formElementPrepend;
    }

    public function setFormElementAppend($formElementAppend)
    {
        $this->_formElementAppend = $formElementAppend;
    }

    public function setFormElementInputClass($formElementInputClass)
    {
        $this->_formElementInputClass = $formElementInputClass;
    }

    public function generateForm($formOptions = null)
    {
        if (!is_array($formOptions))
            $formOptions = array();
        if (!key_exists('method', $formOptions))
            $formOptions['method'] = 'post';
        $strOptions = '';
        foreach ($formOptions as $option => $value)
        {
            $strOptions .= $option . ' = "' . $value . '"';
        }

        $current = false;
        if ($this->_currentId != null)
            $current = $this->_doctrineTable->find($this->_currentId);

        echo '<form ' . $strOptions . '>';
        foreach ($this->_fieldOrder as $name)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            $this->generateFormElement($current, $name, $this->_columns[$name]);
        }
        foreach ($this->_columns as $name => $def)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            if (!in_array($name, $this->_fieldOrder))
                $this->generateFormElement($current, $name, $def);
        }
        echo $this->_formElementPrepend;
        echo '<input type="submit" class="button" />';
        echo $this->_formElementAppend;
        echo '</form>';
    }

    public function generateFormElement($record, $fieldName, $fieldDefinition = null)
    {
        echo $this->_formElementPrepend;
        echo '<label for="' . $fieldName . '" >';
        if (key_exists($fieldName, $this->_fieldLabels))
            echo $this->_fieldLabels[$fieldName];
        else
            echo $fieldName;
        echo ': </label>';
        if (key_exists($fieldName, $this->_relations))
        {
            $foreignTable = Doctrine::getTable($this->_relations[$fieldName]['model']);
            $query = $foreignTable->createQuery();
            $query->select()->orderBy($this->_relations[$fieldName]['display']);

            //$foreignRecords = $foreignTable->findAll();
            $foreignRecords = $query->execute();
            $foreignKey = $this->_relations[$fieldName]['key'];
            $foreignDisplay = $this->_relations[$fieldName]['display'];

            echo '<select name="' . $fieldName . '" id="' . $fieldName . '" class = "' . $this->_formElementInputClass . '">';
            echo '<option value="0">Seleccione...</option>';
            foreach ($foreignRecords as $foreignRecord)
            {
                $selected = '';
                if ($record != false && $foreignRecord->$foreignKey == $record->$fieldName)
                    $selected = ' selected = "selected" ';
                echo '<option value="' . $foreignRecord->$foreignKey . '" ' . $selected . '>' . $foreignRecord->$foreignDisplay . '</option>';
            }
            echo '</select>';
        } else
        {
            if (key_exists($fieldName, $this->_enums))
            {
                echo '<select name="' . $fieldName . '" id="' . $fieldName . '" class = "' . $this->_formElementInputClass . '" >';
                echo '<option value="0">Seleccione...</option>';

                foreach ($this->_enums[$fieldName] as $enumValue => $enumDisplay)
                {
                    $selected = '';
                    if ($record != false && $enumValue == $record->$fieldName)
                        $selected = ' selected = "selected" ';
                    echo '<option value="' . $enumValue . '" ' . $selected . '>' . $enumDisplay . '</option>';
                }
                echo '</select>';
            }
            else
            {
                $typeInput = 'text';
                if (key_exists($fieldName, $this->_fieldInputs))
                    $typeInput = $this->_fieldInputs[$fieldName];
                elseif ($fieldDefinition != null)
                    $typeInput = $this->_columnTypes[$fieldDefinition['type']];

                if ($typeInput == 'textarea')
                {
                    echo '<textarea name="' . $fieldName . '" id="' . $fieldName . '" class = "' . $this->_formElementInputClass . '" >';
                    if ($record != false)
                        echo $record->$fieldName;
                    echo '</textarea>';
                }
                else
                {
                    echo '<input type="' . $typeInput . '" name="' . $fieldName . '" id="' . $fieldName . '"';
                    if ($record != false)
                        echo ' value="' . $record->$fieldName;
                    echo '" class = "' . $this->_formElementInputClass . '" />';
                }
            }
        }
        echo '<br/>';
        echo $this->_formElementAppend;
    }

    public function saveForm($data)
    {
        if ($this->_currentId != null)
            $doctrineModel = $this->_doctrineTable->find($this->_currentId);
        else
            $doctrineModel = new $this->_model;
        foreach ($this->_columns as $name => $def)
        {
            if ($def['autoincrement'] == true)
                continue;
            if (isset($data[$name]))
                $doctrineModel->$name = $data[$name];
        }

        if ($this->_filterField !== null && $this->_filterValue !== null)
        {
            $filterField = $this->_filterField;
            $doctrineModel->$filterField = $this->_filterValue;
        }
        $doctrineModel->save();
    }

    public function deleteRecord($id)
    {
        $record = $this->_doctrineTable->find($id);
        if ($record)
        {
            $record->delete();
        }
    }

    public function generateTable($records = null)
    {
        if ($records == null)
        {
            if ($this->_filterField == null || $this->_filterValue == null)
                $records = $this->_doctrineTable->findAll();
            else
                $records = $this->_doctrineTable->findBy($this->_filterField, $this->_filterValue);
        }
        $identifier = $this->_doctrineTable->getIdentifier();

        echo '<table>';
        echo '<thead>';
        echo '<tr>';

        foreach ($this->_fieldOrder as $name)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            echo '<th>';
            if (key_exists($name, $this->_fieldLabels))
                echo $this->_fieldLabels[$name];
            else
                echo $name;
            echo '</th>';
        }

        foreach ($this->_columns as $name => $def)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            if (!in_array($name, $this->_fieldOrder))
            {
                echo '<th>';
                if (key_exists($name, $this->_fieldLabels))
                    echo $this->_fieldLabels[$name];
                else
                    echo $name;
                echo '</th>';
            }
        }
        if ($this->_editLink)
        {
            echo '<th>';
            echo 'Edit';
            echo '</th>';
        }
        if ($this->_deleteLink)
        {
            echo '<th>';
            echo 'Delete';
            echo '</th>';
        }
        foreach ($this->_externalActions as $nameAction => $externalAction)
        {
            echo '<th>';
            echo $nameAction;
            echo '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($records as $record)
        {
            $editParams = array();
            if ($this->_editController != null)
            {
                $editParams['controller'] = $this->_editController;
            }
            if ($this->_editAction != null)
            {
                $editParams['action'] = $this->_editAction;
            }
            $editParams[$identifier] = $record->$identifier;
            if ($this->_filterField != null && $this->_filterValue != null)
            {
                $editParams['filterValue'] = $this->_filterValue;
            }

            // Se preparan los parametros para la accion delete
            $deleteParams = array();
            if ($this->_deleteController != null)
            {
                $deleteParams['controller'] = $this->_deleteController;
            }
            if ($this->_deleteAction != null)
            {
                $deleteParams['action'] = $this->_deleteAction;
            }
            $deleteParams[$identifier] = $record->$identifier;
            if ($this->_filterField != null && $this->_filterValue != null)
            {
                $deleteParams['filterValue'] = $this->_filterValue;
            }

            echo '<tr>';
            foreach ($this->_fieldOrder as $name)
            {
                if (in_array($name, $this->_ignoreColumns))
                    continue;
                echo '<td>';
                $this->generateTableCell($record, $name, $this->_columns[$name]);
                echo '</td>';
            }

            foreach ($this->_columns as $name => $def)
            {
                if (in_array($name, $this->_ignoreColumns))
                    continue;
                if (!in_array($name, $this->_fieldOrder))
                {
                    echo '<td>';
                    $this->generateTableCell($record, $name, $def);
                    echo '</td>';
                }
            }
            if ($this->_editLink)
            {
                echo '<td>';
                echo '<a class="edit-link" href="' . $this->_view->url($editParams) . '">Editar</a>';
                echo '</td>';
            }
            if ($this->_deleteLink)
            {
                echo '<td>';
                echo '<a class="delete-link" href="' . $this->_view->url($deleteParams) . '">Eliminar</a>';
                echo '</td>';
            }
            foreach ($this->_externalActions as $nameAction => $externalAction)
            {
                $params = $externalAction;
                $params[$identifier] = $record->$identifier;
                if ($this->_filterField != null && $this->_filterValue != null)
                {
                    $params['filterValue'] = $this->_filterValue;
                }
                echo '<td>';
                echo '<a href="' . $this->_view->url($params) . '">' . $nameAction . '</a>';
                echo '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    protected function generateTableCell($record, $fieldName, $fieldDefinition)
    {
        if (key_exists($fieldName, $this->_relations))
        {
            $foreignTable = Doctrine::getTable($this->_relations[$fieldName]['model']);
            $foreignKey = $this->_relations[$fieldName]['key'];
            $foreignDisplay = $this->_relations[$fieldName]['display'];

            if (!key_exists($fieldName, $this->_cache) || !key_exists($record->$fieldName, $this->_cache[$fieldName]))
            {
                $foreignRecord = $foreignTable->find($record->$fieldName);
                if ($foreignRecord)
                {
                    $this->_cache[$fieldName][$record->$fieldName] = $foreignRecord->$foreignDisplay;
                    echo $foreignRecord->$foreignDisplay;
                }
                else
                    $this->_cache[$fieldName][$record->$fieldName] = '';
            }
            else
            {
                echo $this->_cache[$fieldName][$record->$fieldName];
            }
        }
        else
        {
            if (key_exists($fieldName, $this->_enums))
            {
                if (key_exists($record->$fieldName, $this->_enums[$fieldName]))
                    echo $this->_enums[$fieldName][$record->$fieldName];
            }
            else
            {
                echo $record->$fieldName;
            }
        }
    }

    public function generateCSV($records = null, $separator = ",")
    {
        if ($records == null)
        {
            if ($this->_filterField == null || $this->_filterValue == null)
                $records = $this->_doctrineTable->findAll();
            else
                $records = $this->_doctrineTable->findBy($this->_filterField, $this->_filterValue);
        }
        $identifier = $this->_doctrineTable->getIdentifier();

        foreach ($this->_fieldOrder as $name)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            if (key_exists($name, $this->_fieldLabels))
                echo $this->_fieldLabels[$name];
            else
                echo $name;
            echo $separator;
        }

        foreach ($this->_columns as $name => $def)
        {
            if (in_array($name, $this->_ignoreColumns))
                continue;
            if (!in_array($name, $this->_fieldOrder))
            {
                if (key_exists($name, $this->_fieldLabels))
                    echo $this->_fieldLabels[$name];
                else
                    echo $name;
                echo $separator;
            }
        }
        echo "\n";
        foreach ($records as $record)
        {
            foreach ($this->_fieldOrder as $name)
            {
                if (in_array($name, $this->_ignoreColumns))
                    continue;
                $this->generateCSVValue($record, $name, $this->_columns[$name]);
                echo $separator;
            }

            foreach ($this->_columns as $name => $def)
            {
                if (in_array($name, $this->_ignoreColumns))
                    continue;
                if (!in_array($name, $this->_fieldOrder))
                {
                    $this->generateCSVValue($record, $name, $def);
                    echo $separator;
                }
            }
            echo "\n";
        }
    }

    protected function generateCSVValue($record, $fieldName, $fieldDefinition)
    {
        if (key_exists($fieldName, $this->_relations))
        {
            $foreignTable = Doctrine::getTable($this->_relations[$fieldName]['model']);
            $foreignKey = $this->_relations[$fieldName]['key'];
            $foreignDisplay = $this->_relations[$fieldName]['display'];

            if (!key_exists($fieldName, $this->_cache) || !key_exists($record->$fieldName, $this->_cache[$fieldName]))
            {
                $foreignRecord = $foreignTable->find($record->$fieldName);
                if ($foreignRecord)
                {
                    $this->_cache[$fieldName][$record->$fieldName] = $foreignRecord->$foreignDisplay;
                    echo $foreignRecord->$foreignDisplay;
                }
                else
                    $this->_cache[$fieldName][$record->$fieldName] = '';
            }
            else
            {
                echo $this->_cache[$fieldName][$record->$fieldName];
            }
        }
        else
        {
            if (key_exists($fieldName, $this->_enums))
            {
                if (key_exists($record->$fieldName, $this->_enums[$fieldName]))
                    echo $this->_enums[$fieldName][$record->$fieldName];
            }
            else
            {
                echo $record->$fieldName;
            }
        }
    }

}