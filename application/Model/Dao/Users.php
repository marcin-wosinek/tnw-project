<?php

class Dao_Users extends Kwgl_Db_Table {

    public function storeUser($user){
        
        
        
    }
    
    public function getConnectionUsersByOwnerId($ownerId){
        
        $select = new Zend_Db_Select(Zend_Registry::get(DB));
        $select->from(array('c' => 'connections'),array(''));
        $select->joinLeft(array('u' => 'users'), 'u.id = c.connectionId',array('firstName','lastName','id','headLine','pictureUrl'));
        $select->where("c.ownerId = ?", $ownerId);
        
        //echo $select->__toString();
        
        $result = $select->query();
        return $result->fetchAll();
        
    }
    
}