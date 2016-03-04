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
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $ts = (new \DateTime())->getTimestamp();

            $data[] = [
                'username'      => $faker->userName,
                'password'      => password_hash("secret", PASSWORD_BCRYPT, ['cost' => 8]),
                'time_created'  => $ts,
                'last_updated'  => $ts
            ];
        }

        $this->insert('users', $data);
    }
}
