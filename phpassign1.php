<?php
class Connection {
    private $host;
    private $user;
    private $password;
    private $database;
    private $port;

    public function __construct($host, $user, $password, $database, $port = 3306) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    public function getConnectionString() {
        return "mysql:host={$this->host};dbname={$this->database};port={$this->port}";
    }

    public function getUser() {
        return $this->user;
    }

    public function getPassword() {
        return $this->password;
    }

    public function connect() {
        try {
            return new PDO($this->getConnectionString(), $this->user, $this->password);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

class User {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection->connect();
    }

    public function getAllUserData() {
        $query = "SELECT * FROM users";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFullName($userId) {
        $query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(':id', $userId);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['full_name'] : null;
    }

    public function getEmail($userId) {
        $query = "SELECT email FROM users WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(':id', $userId);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['email'] : null;
    }

    public function getId($userId) {
        $query = "SELECT id FROM users WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(':id', $userId);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    public function getDataByField($userId, $field) {
        $query = "SELECT {$field} FROM users WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(':id', $userId);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result ? $result[$field] : null;
    }

    public function isAdult($userId) {
        $dob = $this->getDataByField($userId, 'birthday');
        if ($dob) {
            $dobTimestamp = strtotime($dob);
            $eighteenYearsAgo = strtotime('-18 years');
            return $dobTimestamp <= $eighteenYearsAgo;
        }
        return false;
    }

    public function addUser($userData) {
        $query = "INSERT INTO users (first_name, last_name, email, password, birthday, status) VALUES (:first_name, :last_name, :email, :password, :birthday, :status)";
        $statement = $this->connection->prepare($query);
        $statement->execute([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'password' => $userData['password'],  
            'birthday' => $userData['birthday'],
            'status' => $userData['status']
        ]);
        return $this->connection->lastInsertId();
    }

    public function editUser($userId, $newUserData) {
        $fields = [];
        foreach ($newUserData as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $fieldsString = implode(', ', $fields);
        $query = "UPDATE users SET $fieldsString WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $newUserData['id'] = $userId;
        $statement->execute($newUserData);
    }

    public function changeUserStatus($userId, $newStatus) {
        $query = "UPDATE users SET status = :newStatus WHERE id = :userId";
        $statement = $this->connection->prepare($query);
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':newStatus', $newStatus);
        $statement->execute();
    }
}

$connection = new Connection('localhost', 'root', '', 'my_database');

$user = new User($connection);

$userData = $user->getAllUserData();
$fullName = $user->getFullName(1);
$email = $user->getEmail(1);
$userId = $user->getId(1);
$dataByField = $user->getDataByField(1, 'birthday');
$isAdult = $user->isAdult(1);

$newUserId = $user->addUser([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'password' => 'password',
    'birthday' => '2000-01-01',
    'status' => 1
]);

$user->editUser(1, ['email' => 'john.doe@example.com']);

$user->changeUserStatus(1, 2);

echo "<pre>";
print_r($userData);
echo "</pre>";
echo $fullName !== null ? $fullName : "Full name not found";
echo "<br>";
echo $email !== null ? $email : "Email not found";
echo "<br>";
echo $userId !== null ? $userId : "User ID not found";
echo "<br>";
echo "<pre>";
print_r($dataByField);
echo "</pre>";
echo $isAdult ? 'Korisnik je punoletan' : 'Korisnik je maloletan';
?>
