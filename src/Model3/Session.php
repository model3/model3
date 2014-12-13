<?php
class Model3_Session
{
    public static function start()
    {
        if(sessionExist())
        {
            require_once 'Model3/Exception.php';
            throw new Model3_Exception('Session already exist');
        }
        return session_start();
    }

    public static function destroy()
    {
        return session_destroy();
    }

    public static function sessionExist()
    {
        if(session_id() == '')
            return false;
        return true;
    }
}
