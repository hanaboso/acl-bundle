<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Factory;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Model\User\Event\ActivateUserEvent;

/**
 * Class RuleFactoryTest
 *
 * @package AclBundleTests\Integration\Factory
 *
 * @covers  \Hanaboso\AclBundle\Factory\RuleFactory
 * @coversDefaultClass \Hanaboso\AclBundle\Factory\RuleFactory
 */
final class RuleFactoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers ::createRule
     * @throws Exception
     */
    public function testRuleFactory(): void
    {
        $group = new Group(NULL);
        $group->setName('group');
        $this->pfd($group);

        /** @var Rule $rule */
        $rule = RuleFactory::createRule('user', $group, 3, 2, Rule::class);

        self::assertEquals(3, $rule->getActionMask());
        self::assertEquals(2, $rule->getPropertyMask());
        self::assertEquals('user', $rule->getResource());
    }

    /**
     * @covers ::getDefaultRules
     * @throws Exception
     */
    public function testSetDefaultRules(): void
    {
        $user = new User();
        $user
            ->setEmail('test@test.com')
            ->setPassword('pass');
        $this->pfd($user);
        $a = self::$container->get('event_dispatcher');
        $a->dispatch(new ActivateUserEvent($user), ActivateUserEvent::NAME);

        $res = $this->dm->getRepository(Rule::class)->findBy(
            [
                'group' => $this->dm->getRepository(Group::class)->findOneBy(
                    [
                        'owner' => $user,
                    ]
                ),
            ]
        );

        self::assertCount(2, $res);
        self::assertEquals(3, $res[0]->getActionMask());
        self::assertEquals(3, $res[1]->getActionMask());
    }

}
