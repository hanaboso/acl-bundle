<?php declare(strict_types=1);

namespace AclBundleTests\Integration\Factory;

use AclBundleTests\DatabaseTestCaseAbstract;
use Exception;
use Hanaboso\AclBundle\Document\Group;
use Hanaboso\AclBundle\Document\Rule;
use Hanaboso\AclBundle\Factory\RuleFactory;
use Hanaboso\UserBundle\Document\User;
use Hanaboso\UserBundle\Model\User\Event\ActivateUserEvent;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class RuleFactoryTest
 *
 * @package AclBundleTests\Integration\Factory
 */
#[CoversClass(RuleFactory::class)]
final class RuleFactoryTest extends DatabaseTestCaseAbstract
{

    /**
     * @throws Exception
     */
    public function testRuleFactory(): void
    {
        $group = new Group(NULL);
        $group->setName('group');
        $this->pfd($group);

        /** @var Rule $rule */
        $rule = RuleFactory::createRule('user', $group, 3, 2, Rule::class);

        self::assertSame(3, $rule->getActionMask());
        self::assertSame(2, $rule->getPropertyMask());
        self::assertSame('user', $rule->getResource());
    }

    /**
     * @throws Exception
     */
    public function testSetDefaultRules(): void
    {
        $user = new User();
        $user
            ->setEmail('test@test.com')
            ->setPassword('pass');
        $this->pfd($user);
        $a = self::getContainer()->get('event_dispatcher');
        $a->dispatch(new ActivateUserEvent($user), ActivateUserEvent::NAME);

        $res = $this->dm->getRepository(Rule::class)->findBy(
            [
                'group' => $this->dm->getRepository(Group::class)->findOneBy(
                    [
                        'owner' => $user,
                    ],
                ),
            ],
        );

        self::assertCount(2, $res);
        self::assertSame(3, $res[0]->getActionMask());
        self::assertSame(3, $res[1]->getActionMask());
    }

}
