<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Document;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\UserBundle\Document\User;

/**
 * Class DocumentTest
 *
 * @package AclBundleTests\Integration\Document
 */
final class DocumentTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testReferences(): void
    {
        $user = (new User())->setEmail('email@example.com');
        $this->pfd($user);

        $group = (new Group($user))
            ->setName('Group')
            ->addUser($user);
        $this->pfd($group);

        $rule = (new Rule())
            ->setResource('R1')
            ->setGroup($group);
        $this->pfd($rule);

        $group->addRule($rule);
        $this->dm->flush();
        $this->dm->clear();

        /** @var Group $existingGroup */
        $existingGroup = $this->dm->getRepository(Group::class)->find($group->getId());

        self::assertSame($group->getName(), $existingGroup->getName());
        self::assertCount(1, $group->getUsers());
        self::assertSame(
            $group->getUsers()->toArray()[0]->getEmail(),
            $existingGroup->getUsers()->toArray()[0]->getEmail(),
        );
        self::assertCount(1, $group->getRules());
        self::assertSame(
            $group->getRules()->toArray()[0]->getResource(),
            $existingGroup->getRules()->toArray()[0]->getResource(),
        );
    }

}
