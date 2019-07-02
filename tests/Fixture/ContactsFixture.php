<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ContactsFixture
 *
 */
class ContactsFixture extends TestFixture
{
    public $import = ['model' => 'Contacts'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'type' => 'individual',
            'gender' => 'm',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'company_name' => '',
            'nationality' => 'GB',
            'description' => '',
            'birthdate' => '1975-11-07',
            'primary' => 1,
            'location' => '',
            'email' => 'john.smith@company.com',
            'phone' => '9988776655',
            'street' => '',
            'street_2' => '',
            'city' => 'London',
            'state' => '',
            'post_code' => '',
            'country' => 'GB',
            'website' => '',
            'created' => '2017-11-07 12:40:58',
            'modified' => '2017-11-07 12:40:58',
            'trashed' => null,
            'created_by' => '00000000-0000-0000-0000-000000000002',
            'modified_by' => '00000000-0000-0000-0000-000000000001'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'type' => 'organization',
            'gender' => '',
            'first_name' => '',
            'last_name' => '',
            'company_name' => 'Google',
            'nationality' => '',
            'description' => '',
            'birthdate' => '',
            'primary' => 1,
            'location' => '',
            'email' => '',
            'phone' => '',
            'street' => '1600 Amphitheatre Parkway',
            'street_2' => 'Mountain View',
            'city' => 'San Francisco',
            'state' => 'California',
            'post_code' => 'CA 94043',
            'country' => 'US',
            'website' => 'https://www.google.com',
            'created' => '2017-11-07 12:40:58',
            'modified' => '2017-11-07 12:45:58',
            'trashed' => null,
            'created_by' => '00000000-0000-0000-0000-000000000002',
            'modified_by' => '00000000-0000-0000-0000-000000000001'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'type' => 'individual',
            'gender' => 'm',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'company_name' => '',
            'nationality' => 'GB',
            'description' => '',
            'birthdate' => '1975-11-07',
            'primary' => 1,
            'location' => '',
            'email' => 'john.smith@company.com',
            'phone' => '9988776655',
            'street' => '',
            'street_2' => '',
            'city' => 'London',
            'state' => '',
            'post_code' => '',
            'country' => 'GB',
            'website' => '',
            'created' => '2017-11-07 12:40:58',
            'modified' => '2017-11-07 12:40:58',
            'trashed' => null,
            'created_by' => '00000000-0000-0000-0000-000000000002',
            'modified_by' => '00000000-0000-0000-0000-000000000001'
        ],
    ];
}
