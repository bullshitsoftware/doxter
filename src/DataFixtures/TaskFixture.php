<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Service\DateTime\DateTimeFactory;
use function array_key_exists;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    private const data = [
        'task_current_1' => [
            'id' => '2c2bbc1d-e729-4fde-935f-2f5faca6d905',
            'tags' => ['bar', 'foo'],
            'title' => 'Current task 1',
            'created' => '-9 minutes',
            'started' => '-9 minutes',
            'wait' => '-1 second',
        ],
        'task_current_2' => [
            'id' => '5aa61370-a209-4f00-9c6e-145b57ce138b',
            'tags' => ['foo'],
            'title' => 'Current task 2',
            'created' => '-8 minutes',
        ],
        'task_current_3' => [
            'id' => 'b3bb6502-00a5-4fb6-b34b-3df5867a006b',
            'tags' => ['baz'],
            'title' => 'Current task 3',
            'created' => '-7 minutes',
            'started' => '-7 minutes',
        ],
        'task_current_4' => [
            'id' => '288d7410-ff1a-4fef-aabf-d4a3e1774002',
            'title' => 'Current task 4',
            'created' => '-6 minutes',
        ],
        'task_current_5' => [
            'id' => '8570760c-9217-4f78-bfdf-c41476387f6e',
            'title' => 'Current task 5',
            'created' => '-5 minutes',
            'started' => '-5 minutes',
        ],
        'task_current_6' => [
            'id' => '738c8de9-a109-4fc9-90b4-28a5b984069d',
            'title' => 'Current task 6',
            'created' => '-4 minutes',
        ],
        'task_current_7' => [
            'id' => 'a0e84ad1-546a-4f5c-88bb-1bfd5d74e19c',
            'title' => 'Current task 7',
            'created' => '-3 minutes',
            'started' => '-3 minutes',
        ],
        'task_current_8' => [
            'id' => '1daf6745-df64-4fc1-a062-e613af55901e',
            'title' => 'Current task 8',
            'created' => '-2 minutes',
            'due' => '+8 month -1 minute',
        ],
        'task_current_9' => [
            'id' => '8670b12e-0fa8-4fb9-ab16-e121cc3d9dd9',
            'title' => 'Current task 9',
            'created' => '-1 minute',
            'started' => '-1 minute',
            'due' => '+9 month -1 minute',
        ],

        'task_waiting_1' => [
            'id' => '1d44a8c5-e126-4f42-ab51-b8d2215049e3',
            'tags' => ['bar', 'foo'],
            'title' => 'Delayed task 1',
            'created' => '-8 minutes',
            'wait' => '-8 minutes +1 day',
        ],
        'task_waiting_2' => [
            'id' => '32a50e63-58fc-4f65-8683-7b08fb7c9df1',
            'tags' => ['foo'],
            'title' => 'Delayed task 2',
            'created' => '-7 minutes',
            'wait' => '-7 minutes +2 days',
        ],
        'task_waiting_3' => [
            'id' => 'babaad5b-3db2-4f4f-9ba7-f98466309c9b',
            'tags' => ['baz'],
            'title' => 'Delayed task 3',
            'created' => '-6 minutes',
            'wait' => '-6 minutes +3 days',
        ],
        'task_waiting_4' => [
            'id' => '6d6cb3f2-e6c8-4f17-bf7c-05935d6b7913',
            'title' => 'Delayed task 4',
            'created' => '-5 minutes',
            'wait' => '-5 minutes +4 days',
        ],
        'task_waiting_5' => [
            'id' => '004b3576-dd6c-4fb3-a255-d44766e1608a',
            'title' => 'Delayed task 5',
            'created' => '-4 minutes',
            'wait' => '-4 minutes +5 days',
        ],
        'task_waiting_6' => [
            'id' => '67fa1571-2a5a-4f0e-8de8-2d0f6927a532',
            'title' => 'Delayed task 6',
            'created' => '-3 minutes',
            'wait' => '-3 minutes +6 days',
        ],
        'task_waiting_7' => [
            'id' => '35482f30-22c2-4fa8-8bce-feb2aed81313',
            'title' => 'Delayed task 7',
            'created' => '-2 minutes',
            'wait' => '-2 minutes +7 days',
        ],
        'task_waiting_8' => [
            'id' => 'e8966f17-d167-4fd3-8326-4221ddb34a45',
            'title' => 'Delayed task 8',
            'created' => '-1 minute',
            'wait' => '-1 minute +8 days',
        ],

        'task_completed_1' => [
            'id' => 'd74c0d03-a5d7-4fec-accc-4573d8c55878',
            'tags' => ['bar', 'foo'],
            'title' => 'Done task 1',
            'created' => '-10 days',
            'ended' => '-9 days +12 hours',
        ],
        'task_completed_2' => [
            'id' => '997a2edd-b8fe-4fe8-be92-34957b4ccb3f',
            'tags' => ['foo'],
            'title' => 'Done task 2',
            'created' => '-9 days',
            'ended' => '-9 days +12 hours',
        ],
        'task_completed_3' => [
            'id' => '14936392-2c17-4fb5-9618-aafc6346ea3e',
            'tags' => ['baz'],
            'title' => 'Done task 3',
            'created' => '-8 days',
            'ended' => '-7 days +12 hours',
        ],
        'task_completed_4' => [
            'id' => '85114a9d-a410-4f28-9ee0-0a1f102e65e6',
            'title' => 'Done task 4',
            'created' => '-7 days',
            'ended' => '-7 days +12 hours',
        ],
        'task_completed_5' => [
            'id' => 'bed3c6d8-e787-4f05-a4ef-0964776ac671',
            'title' => 'Done task 5',
            'created' => '-6 days',
            'ended' => '-5 days +12 hours',
        ],
        'task_completed_6' => [
            'id' => '2a15b215-921c-4f0d-ac8e-183bddcd4a81',
            'title' => 'Done task 6',
            'created' => '-5 days',
            'ended' => '-5 days +12 hours',
        ],
        'task_completed_7' => [
            'id' => 'dcc2a5e5-11e8-4f3d-8d3b-012066d71a2f',
            'title' => 'Done task 7',
            'created' => '-4 days',
            'ended' => '-3 days +12 hours',
        ],
        'task_completed_8' => [
            'id' => '7ae9db23-e273-4f16-ae82-db1966921dbc',
            'title' => 'Done task 8',
            'created' => '-3 days',
            'ended' => '-3 days +12 hours',
        ],
        'task_completed_9' => [
            'id' => '876b7262-4670-4fc6-a917-ded027f2f877',
            'title' => 'Done task 9',
            'created' => '-2 day',
            'ended' => '-1 day +12 hours',
        ],
        'task_completed_10' => [
            'id' => 'da6dad30-7d09-4f0a-9eb5-6e40e0870210',
            'title' => 'Done task 10',
            'created' => '-1 day',
            'ended' => '-1 day +12 hours',
        ],
    ];

    public function __construct(private DateTimeFactory $dateTimeFactory)
    {
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }

    public function load(ObjectManager $manager): void
    {
        $now = $this->dateTimeFactory->now();
        $user = $this->getReference(UserFixture::JOHN_DOE);

        foreach (self::data as $reference => $data) {
            $task = new Task();
            $task->setId(Uuid::fromString($data['id']));
            $task->setTags($data['tags'] ?? []);
            $task->setUser($user);
            $task->setTitle($data['title']);
            $task->setCreated(
                array_key_exists('created', $data) ? $now->modify($data['created']) : $now,
            );
            $task->setUpdated(
                array_key_exists('updated', $data) ? $now->modify($data['updated']) : $task->getCreated(),
            );
            $task->setWait(
                array_key_exists('wait', $data) ? $now->modify($data['wait']) : null,
            );
            $task->setStarted(
                array_key_exists('started', $data) ? $now->modify($data['started']) : null,
            );
            $task->setEnded(
                array_key_exists('ended', $data) ? $now->modify($data['ended']) : null,
            );
            $task->setDue(
                array_key_exists('due', $data) ? $now->modify($data['due']) : null,
            );
            $manager->persist($task);
            $this->addReference($reference, $task);
        }

        $manager->flush();
    }

    public static function referenceName(string $list, int $i): string
    {
        return sprintf('task_%s_%d', $list, $i);
    }
}
