<?php

/** Display server list instead of separate input fields.
 * Safe (e.g., dev) and unsafe (e.g., production) environments are supported.
 *
 * @link https://github.com/plashenkov/Adminer-ServerList
 * @author Yuri Plashenkov, https://plashenkov.com
 * @license https://mit.plashenkov.com MIT License
 */
class ServerList
{
    private $servers = [];
    private $safeEnv = false;
    private $hidePermanentLogin = true;

    public function __construct(array $servers, $safeEnv = false, $hidePermanentLogin = true)
    {
        $this->servers = $servers;
        $this->safeEnv = $safeEnv;
        $this->hidePermanentLogin = $hidePermanentLogin;

        $key = $_POST['auth']['server'] ?? null;
        if (empty($key)) return;

        $driver = $this->servers[$key]['driver'] ?? 'server';
        if ($driver === 'mysql') $driver = 'server';

        $_POST['auth']['driver'] = $driver;
        $_POST['auth']['db'] = $this->servers[$key]['db'] ?? '';
    }

    public function loginForm()
    {
        if ($this->hidePermanentLogin) echo '<style>#logins {display: none}</style>';
        echo '<table cellspacing="0" class="layout">';
        echo '<tr><th>' . lang('Server') . '<td><select name="auth[server]">' . optionlist(array_keys($this->servers), SERVER ?: getenv('ADMINER_DEFAULT_SERVER')) . '</select>';
        if (!$this->safeEnv) {
            echo '<tr><th>' . lang('Username') . '<td><input name="auth[username]" id="username" value="' . h($_GET['username']) . '" autocomplete="username" autocapitalize="off">' . script("focus(qs('#username')); qs('#username').form['auth[driver]'].onchange();");
		    echo '<tr><th>' . lang('Password') . '<td><input type="password" name="auth[password]" autocomplete="current-password">';
        }
        echo '</table><p><input type="submit" value="' . lang('Login') . '">';
        if (!$this->hidePermanentLogin) {
            echo checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang('Permanent login'));
        }
        return true;
    }

    public function login($login, $password)
    {
        if (empty($this->servers[SERVER]['server'])) return false;
        if ($this->safeEnv || !empty($password)) return true;
        if (!empty($this->servers[SERVER]['password'])) return false;
    }

    public function credentials()
    {
        if ($this->safeEnv) {
            return [
                $this->servers[SERVER]['server'],
                $this->servers[SERVER]['username'] ?? '',
                $this->servers[SERVER]['password'] ?? ''
            ];
        }

        $password = get_password();
        $passwordHash = $this->servers[SERVER]['password'] ?? '';

        return [
            $this->servers[SERVER]['server'],
            $_GET['username'],
            $passwordHash && password_verify($password, $passwordHash) ? '' : $password
        ];
    }
}
