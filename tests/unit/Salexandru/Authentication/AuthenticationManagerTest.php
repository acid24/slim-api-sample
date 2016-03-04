<?php

namespace Salexandru\Authentication;

use Mockery as m;

class AuthenticationManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testAuthentication()
    {
        $subject = m::mock(SubjectInterface::class);
        $expectedResult = Result::success();

        $strategy = m::mock(AuthenticationStrategyInterface::class)
            ->shouldReceive('authenticate')
            ->once()
            ->with($subject)
            ->andReturn($expectedResult)
            ->getMock();

        $manager = new AuthenticationManager($strategy);
        $actualResult = $manager->authenticate($subject);

        $this->assertSame($expectedResult, $actualResult);
    }
}
