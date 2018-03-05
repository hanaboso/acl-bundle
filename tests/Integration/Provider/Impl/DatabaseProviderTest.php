<?php declare(strict_types=1);

namespace Tests\Integration\Provider\Impl;

use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Provider\Impl\DatabaseProvider;
use Hanaboso\UserBundle\Document\User;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class DatabaseProviderTest
 *
 * @package Tests\Integration\Provider\Impl
 */
class DatabaseProviderTest extends DatabaseTestCaseAbstract
{

    /**
     *
     */
    public function testGetRules(): void
    {
        /** @var DatabaseProvider $databaseProvider */
        $databaseProvider = $this->container->get('hbpf.acl.provider.database');

        $ruleOne = (new Rule())->setResource('R1');
        $this->persistAndFlush($ruleOne);
        $ruleTwo = (new Rule())->setResource('R2');
        $this->persistAndFlush($ruleTwo);
        $ruleThree = (new Rule())->setResource('R3');
        $this->persistAndFlush($ruleThree);
        $ruleFour = (new Rule())->setResource('R4');
        $this->persistAndFlush($ruleFour);

        $groupOne = (new Group(NULL))
            ->setName('G1')
            ->addRule($ruleOne)
            ->addRule($ruleThree);
        $ruleOne->setGroup($groupOne);
        $ruleThree->setGroup($groupOne);
        $this->persistAndFlush($groupOne);

        $groupTwo = (new Group(NULL))
            ->setName('G1')
            ->addRule($ruleTwo)
            ->addRule($ruleFour);
        $ruleTwo->setGroup($groupTwo);
        $ruleFour->setGroup($groupTwo);
        $this->persistAndFlush($groupTwo);

        $user = (new User())->setEmail('user@example.com');
        $this->persistAndFlush($user);

        $groupOne->addUser($user);
        $groupTwo->addUser($user);

        $this->dm->flush();

        $rules = $databaseProvider->getRules($user);

        $this->assertEquals(4, count($rules));
        $this->assertEquals($ruleOne->getResource(), $rules[0]->getResource());
        $this->assertEquals($ruleOne->getGroup()->getName(), $rules[0]->getGroup()->getName());
        $this->assertEquals(1, count($rules[0]->getGroup()->getUsers()));
        $this->assertEquals(
            $ruleOne->getGroup()->getUsers()[0]->getEmail(),
            $rules[0]->getGroup()->getUsers()[0]->getEmail()
        );
    }

}