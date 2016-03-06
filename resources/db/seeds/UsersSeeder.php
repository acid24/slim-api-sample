<?php

use Phinx\Seed\AbstractSeed;

class UsersSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $ts = (new \DateTime())->getTimestamp();

        $user1 = [
            'username' => 'user1',
            'password' => password_hash("secret", PASSWORD_BCRYPT, ['cost' => 8]),
            'time_created'  => $ts,
            'last_updated'  => $ts
        ];

        $user2 = [
            'username' => 'user2',
            'password' => password_hash("secret", PASSWORD_BCRYPT, ['cost' => 8]),
            'time_created'  => $ts,
            'last_updated'  => $ts
        ];

        $user3 = [
            'username' => 'user3',
            'password' => password_hash("secret", PASSWORD_BCRYPT, ['cost' => 8]),
            'time_created'  => $ts,
            'last_updated'  => $ts
        ];

        $this->insert('users', $user1);
        $this->insert('users', $user2);
        $this->insert('users', $user3);
    }
}
