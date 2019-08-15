<?php declare(strict_types=1);

namespace Tests\Integration\Document;

use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\UserBundle\Document\User;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class DocumentTest
 *
 * @package Tests\Integration\Document
 */
final class DocumentTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testReferences(): void
    {
        $user = (new User())->setEmail('email@example.com');
        $this->persistAndFlush($user);

        $group = (new Group($user))
            ->setName('Group')
            ->addUser($user);
        $this->persistAndFlush($group);

        $rule = (new Rule())
            ->setResource('R1')
            ->setGroup($group);
        $this->persistAndFlush($rule);

        $group->addRule($rule);
        $this->dm->flush();
        $this->dm->clear();

        /** @var Group $existingGroup */
        $existingGroup = $this->dm->getRepository(Group::class)->find($group->getId());

        $this->assertSame($group->getName(), $existingGroup->getName());
        $this->assertCount(1, $group->getUsers());
        $this->assertSame($group->getUsers()[0]->getEmail(), $existingGroup->getUsers()[0]->getEmail());
        $this->assertCount(1, $group->getRules());
        $this->assertSame($group->getRules()[0]->getResource(), $existingGroup->getRules()[0]->getResource());
    }

}
