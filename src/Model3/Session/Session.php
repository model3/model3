<?php

namespace Model3\Session;

use Model3\Exception\Model3Exception;

class Session
{
    public static function start()
    {
        if(Session::sessionExist())
        {
            throw new Model3Exception('Session already exist');
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
