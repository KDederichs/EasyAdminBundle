<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Router\Fixtures;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

/**
 * Fixture dashboard with an invalid 'detail' route path: it contains neither the canonical
 * "{entityId}" placeholder nor its "{id}" alias ("{userId}" must not be matched as such).
 */
#[AdminDashboard(routePath: '/invalid-admin', routeName: 'invalid_admin', routes: [
    'detail' => ['routePath' => '/{userId}'],
])]
class InvalidDetailPathDashboardController extends AbstractDashboardController
{
}
