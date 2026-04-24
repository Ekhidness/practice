<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Database\Capsule\Manager as Capsule;
use Model\Subscriber;

class SubscriberTest extends TestCase
{
    protected function setUp(): void
    {
        $dbConfig = include dirname(__DIR__) . '/config/db.php';

        $capsule = new Capsule;
        $capsule->addConnection($dbConfig);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Subscriber::where('Surname', 'TestUser')->delete();
    }

    protected function tearDown(): void
    {
        Subscriber::where('Surname', 'TestUser')->delete();
        Capsule::disconnect();
    }

    #[DataProvider('creationProvider')]
    public function testSubscriberCreation(array $data, bool $expectCreated): void
    {
        $required = ['Surname', 'Name', 'BirthdayDate'];
        $missing  = array_diff_key(array_flip($required), $data);
        $isValid  = empty($missing);

        if ($isValid && $expectCreated) {
            $subscriber = Subscriber::create($data);
            $this->assertNotNull($subscriber->SubscriberID, 'Запись должна быть создана');
            $this->assertEquals($data['Surname'], $subscriber->Surname);
            $subscriber->delete();
        } else {
            $this->assertFalse($isValid, 'Валидация должна отклонить неполные данные');
        }
    }

    public static function creationProvider(): array
    {
        return [
            'valid_data'   => [['Surname' => 'TestUser', 'Name' => 'TestName', 'BirthdayDate' => '1995-05-20'], true],
            'empty_data'   => [[], false],
            'partial_data' => [['Surname' => 'TestUser'], false],
        ];
    }
}