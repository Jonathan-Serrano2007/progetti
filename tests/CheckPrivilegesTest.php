<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../sito progetti/Informatica/musicare/check_privileges.php';

class DummyStmt {
    private $rows;
    public function __construct($rows = []) { $this->rows = $rows; }
    public function execute($params = null) { return true; }
    public function fetchAll($mode = null) { return $this->rows; }
}

class DummyPDO {
    private $rows;
    public function __construct($rows = []) { $this->rows = $rows; }
    public function prepare($sql) { return new DummyStmt($this->rows); }
}

class CheckPrivilegesTest extends TestCase
{
    protected function setUp(): void
    {
        // ensure session globals
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        global $pdo;
        $pdo = null;
        parent::tearDown();
    }

    public function testIsLoggedInFalseByDefault()
    {
        $this->assertFalse(is_logged_in());
        $this->assertEquals('guest', get_user_role());
        $this->assertNull(get_user_id());
        $this->assertEquals('Ospite', get_user_name());
    }

    public function testIsLoggedInAndRoleHelpers()
    {
        $_SESSION['utente_id'] = 42;
        $_SESSION['utente_ruolo'] = 'pro';
        $_SESSION['utente_nome'] = 'Mario';

        $this->assertTrue(is_logged_in());
        $this->assertEquals('pro', get_user_role());
        $this->assertEquals(42, get_user_id());
        $this->assertEquals('Mario', get_user_name());
        $this->assertTrue(is_pro());
        $this->assertFalse(is_admin());
    }

    public function testGetUserPrivilegesAndCheck()
    {
        $_SESSION['utente_id'] = 7;
        $_SESSION['utente_ruolo'] = 'student';

        // Mock PDO to return two privileges
        $rows = [ ['nome_privilegio' => 'svolge_esercizi_base'], ['nome_privilegio' => 'view_dashboard'] ];
        global $pdo;
        $pdo = new DummyPDO($rows);

        $privs = get_user_privileges();
        $this->assertContains('svolge_esercizi_base', $privs);
        $this->assertContains('view_dashboard', $privs);
        $this->assertTrue(check_privilege('svolge_esercizi_base'));
        $this->assertFalse(check_privilege('non_esiste'));
    }
}
