<?php
/*
    from php.net
    https://secure.php.net/manual/en/features.http-auth.php
*/
class digest
{
    private $realm;
    private $users;
    private $message;

    CONST MAX_LIFETIME = 90; // in seconds
    CONST MAX_COUNT = 100; // accesses to the page

    public function __construct($realm, $users, $message)
    {
        $this->setRealm($realm);
        $this->setUsers($users);
        $this->setMessage($message);
    }

    public function init()
    {
        if (!isset($_SESSION['authok'])) {
            $_SESSION['authok'] = true;
        }
    }

    public function logout()
    {
        $_SESSION['authok'] = false;
        header('location: /');
        exit();
    }

    public function checkAuth()
    {
        if (empty($_SERVER['PHP_AUTH_DIGEST']) ||
            $_SESSION['authok'] == false ||
            $_SESSION['authcount']++ >= self::MAX_COUNT ||
            time() - $_SESSION['authtime'] >= self::MAX_LIFETIME
        ) {
            unset($_SESSION['authok']);
            $_SESSION['authtime'] = time();
            $_SESSION['authcount'] = 0;
            $this->resetNonce();
        }
    }

        private function resetNonce()
        {
            $_SESSION['authnonce'] = bin2hex(openssl_random_pseudo_bytes(20));
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$this->getRealm().
                '",qop="auth",nonce="'.$_SESSION['authnonce'].'",opaque="'.md5($this->getRealm()).'"');

            die($this->getMessage());
        }

    public function response()
    {
        // analyze the PHP_AUTH_DIGEST variable
        if (!($data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))
            || !isset($this->getUsers()[$data['username']])
        ) {
            die($this->message);
        }

        // generate the valid response
        $A1 = md5(
            $data['username'] . ':' . $this->getRealm() . ':' . $this->getUsers()[$data['username']]
        );
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5(
            $A1 . ':' . $_SESSION['authnonce'] . ':' . $data['nc'] . ':'
            . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2
        );

        if ($data['response'] != $valid_response) {
            die($this->message);
        }
    }

        // function to parse the http auth header
        private function http_digest_parse($txt)
        {
            // protect against missing data
            $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
            $data = array();
            $keys = implode('|', array_keys($needed_parts));

            preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $data[$m[1]] = $m[3] ? $m[3] : $m[4];
                unset($needed_parts[$m[1]]);
            }

            return $needed_parts ? false : $data;
        }

    public function getRealm()
    {
        return $this->realm;
    }

    public function setRealm($realm)
    {
        $this->realm = $realm;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}
