<?php declare(strict_types=1);

namespace Tests\Integration\Factory;

use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Model\User\Event\UserEvent;
use Tests\DatabaseTestCaseAbstract;

/**
 * Class RuleFactoryTest
 *
 * @package Tests\Integration\Factory
 */
final class RuleFactoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers ::createRule()
     */
    public function testRuleFactory(): void
    {
        $group = new Group(NULL);
        $group->setName('group');
        $this->persistAndFlush($group);

        $fac = $this->c->get('hbpf.factory.rule');
        /** @var Rule $rule */
        $rule = $fac->createRule('user', $group, 3, 2, Rule::class);

        self::assertInstanceOf(Rule::class, $rule);
        self::assertEquals(3, $rule->getActionMask());
        self::assertEquals(2, $rule->getPropertyMask());
        self::assertEquals('user', $rule->getResource());
    }

    /**
     * @covers ::getDefaultRules()
     * @throws Exception
     */
    public function testSetDefaultRules(): void
    {
        $user = new User();
        $user
            ->setEmail('test@test.com')
            ->setPassword('pass');
        $this->persistAndFlush($user);
        $this->c->get('event_dispatcher')->dispatch(UserEvent::USER_ACTIVATE, new UserEvent($user));

        $res = $this->dm->getRepository(Rule::class)->findBy([
            'group' => $this->dm->getRepository(Group::class)->findOneBy([
                'owner' => $user,
            ]),
        ]);

        self::assertCount(2, $res);
        self::assertEquals(3, $res[0]->getActionMask());
        self::assertEquals(3, $res[1]->getActionMask());
    }

}
