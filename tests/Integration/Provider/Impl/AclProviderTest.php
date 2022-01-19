<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Provider\Impl;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Provider\Impl\AclProvider;
use Hanaboso\UserBundle\Document\User;

/**
 * Class AclProviderTest
 *
 * @package AclBundleTests\Integration\Provider\Impl
 */
final class AclProviderTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testGetRules(): void
    {
        /** @var AclProvider $databaseProvider */
        $databaseProvider = self::getContainer()->get('hbpf.acl.provider');

        $ruleOne = (new Rule())->setResource('R1');
        $this->pfd($ruleOne);
        $ruleTwo = (new Rule())->setResource('R2');
        $this->pfd($ruleTwo);
        $ruleThree = (new Rule())->setResource('R3');
        $this->pfd($ruleThree);
        $ruleFour = (new Rule())->setResource('R4');
        $this->pfd($ruleFour);

        $groupOne = (new Group(NULL))
            ->setName('G1')
            ->addRule($ruleOne)
            ->addRule($ruleThree);
        $ruleOne->setGroup($groupOne);
        $ruleThree->setGroup($groupOne);
        $this->pfd($groupOne);

        $groupTwo = (new Group(NULL))
            ->setName('G1')
            ->addRule($ruleTwo)
            ->addRule($ruleFour);
        $ruleTwo->setGroup($groupTwo);
        $ruleFour->setGroup($groupTwo);
        $this->pfd($groupTwo);

        $user = (new User())->setEmail('user@example.com');
        $this->pfd($user);

        $groupOne->addUser($user);
        $groupTwo->addUser($user);

        $this->dm->flush();

        $int   = 9_999;
        $rules = $databaseProvider->getRules($user, $int);
        self::assertLessThanOrEqual(9_999, $int);

        self::assertEquals(4, count($rules));
        self::assertEquals($ruleOne->getResource(), $rules[0]->getResource());
        self::assertEquals($ruleOne->getGroup()->getName(), $rules[0]->getGroup()->getName());
        self::assertEquals(1, count($rules[0]->getGroup()->getUsers()));
        self::assertEquals(
            $ruleOne->getGroup()->getUsers()->toArray()[0]->getEmail(),
            $rules[0]->getGroup()->getUsers()->toArray()[0]->getEmail(),
        );
    }

}
