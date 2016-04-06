<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 *
 * JeDbObject: just enough database object
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2011/2014, Nurogames GmbH
 */
// ****************************************************************************
//ToDo: StyleGuide ... , UpperCamelCase...(?)

// CLASS ======================================================================
/**
 * @brief fields/lists handler
 */
class JeDbObject extends stdClass
{
    /// @brief member id
    private $m_nId;

    /// @brief table
    private $m_sTable;

    /// @brief index field
    private $m_sIndexField;

    /// @brief joins array
    private $m_aJoins;

    /// @brief fields
    public $fields;

    /// @brief joins
    public $joins;

    /// @brief lists
    public $lists;

    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief gets db connection
     * 
     * @param $sClass - class string
     * @param $nId  - id
     * @param $aJoins - joins array
     * @param $aLists - lists array
     * @param $sFields - field string
     * 
     * @return NULL
     */
    public function __construct( $sClass, $nId, $aJoins = array(), $aLists = array(), $sFields = '*' )
    {

        $sTable = $sClass . 's';

        $sIndexField = $sClass . '_id';

        $kDb = JeDb::getInstance();

        $sSql = 'select ' . $sFields . ' from ' . $sTable . ' where ' . $sIndexField . ' = "' . $nId . '"';

        $this->fields = $kDb->fetchFirstRow($sSql);

        $this->m_nId = $nId;

        if ( $this->fields->$sIndexField != $nId)
        {
            $this->m_nId = NULL;

            return NULL;
        }

        $this->m_sTable = $sTable;

        $this->m_sIndexField = $sIndexField;

        $this->m_aJoins = $aJoins;

        $this->joins = new stdClass();

        $this->lists = new stdClass();

        foreach ( $aJoins as $sJoin)
        {
            $sJoinTable = $sJoin . 's';

            $sJoinId = $sJoin . '_id';

            $this->joins->$sJoin = new JeDbObject( $sJoin, $this->fields->$sJoinId);
        }

        foreach ( $aLists as $sJoinTable)
        {
            $this->loadList( $sJoinTable);
        }

    }

    // ------------------------------------------------------------------------
    /**
     * @brief loads list
     * 
     * @param $sJoinTable - joinTable string
     */
    public function loadList( $sJoinTable)
    {
        $kDb = JeDb::getInstance();

        $sSql = 'select * from ' . $sJoinTable . ' where ' . $this->m_sIndexField . ' = "' . $this->Id() . '"';

        $this->lists->$sJoinTable = $kDb->fetchAllRows( $sSql);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief returns id
     * 
     * @return id
     */
    public function Id()
    {
        return $this->m_nId;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief saves fields / updates db
     * 
     * @param type $sFieldName
     */
    public function saveField( $sFieldName)
    {
        $kDb = JeDb::getInstance();

        $kValue = $this->fields->$sFieldName;

        $sSql = 'update ' . $this->m_sTable
              . ' set '
              . $sFieldName . ' = "' . $kDb->escape_string ( $kValue) .'" '
              . ' where '
              . $this->m_sIndexField . ' = "' . $this->m_nId . '";';

        $kDb->update( $sSql);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief save fields / updates db / without use of mysql_escape_string 
     * 
     * @param $sFieldName - FieldName string
     * @param $sValueString - value string
     */
    public function saveFieldRaw( $sFieldName, $sValueString = 'NULL')
    {
        $kDb = JeDb::getInstance();

        $sSql = 'update ' . $this->m_sTable
              . ' set '
              . $sFieldName . ' = ' . $sValueString
              . ' where '
              . $this->m_sIndexField . ' = "' . $this->m_nId . '";';

        $kDb->update( $sSql);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief updates field
     * 
     * @param $sFieldName - FieldName string
     * @param $kValue - value object
     */
    public function updateField($sFieldName, $kValue)
    {
        $this->fields->$sFieldName = $kValue;

        $this->saveField( $sFieldName);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief increment field
     * 
     * @param $sFieldName - fieldName
     * @param $kValue - value object
     * 
     * @return incremented fieldnames
     */
    public function incrementField($sFieldName, $kValue = 1)
    {
        $this->fields->$sFieldName += $kValue;

        $this->saveField( $sFieldName);

        return $this->fields->$sFieldName;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief decrement field
     * 
     * @param $sFieldName - fieldName string
     * @param $kValue - object value 
     */
    public function decrementField($sFieldName, $kValue = 1)
    {
        $this->fields->$sFieldName -= $kValue;

        $this->saveField( $sFieldName);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief lists item by key
     * 
     * @param $sListName - ListName string
     * @param $sKeyName - KeyName string
     * @param $kKey - Key object
     * 
     * @return found items
     */
    public function listItemByKey ($sListName, $sKeyName, $kKey)
    {
        $kFoundItem = null;

        foreach ($this->lists->$sListName as $kListItem)
        {
            if( $kListItem->$sKeyName == $kKey)
            {
                $kFoundItem = $kListItem;

                break;
            }
        }

        return $kFoundItem;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief increment list item filtered by key
     * 
     * @param $sListName - ListName string
     * @param $sKeyName - KeyName string
     * @param $sKey - Key string
     * @param $sField - Field string
     * @param $nValue - value number
     * 
     * @return boolean
     */
    public function incrementListItemByKey ($sListName, $sKeyName, $sKey, $sField, $nValue = 1)
    {
        //$kFoundItem = ListItemByKey ($sListName, $sKeyName, $kKey);

        //Todo SQL
        $sSql = 'UPDATE ' .$sListName
              . ' SET ' .$sField . ' = ' .$sField . ' + "' . $nValue . '" '
              . ' WHERE ' . $this->m_sIndexField . ' = "' . $this->Id() . '" '
              . ' AND ' . $sKeyName . ' = "' . $sKey . '"'
        ;

        echo $sSql;

        $kDb = JeDb::getInstance();

        $kDb->update( $sSql);

        $this->loadList( $sListName);

        return true;

    }

}
