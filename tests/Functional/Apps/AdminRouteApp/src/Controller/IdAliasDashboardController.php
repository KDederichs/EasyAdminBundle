<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\AdminRouteApp\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

/**
 * Dashboard controller that customizes the paths of built-in entity actions using
 * the "{id}" placeholder (alias of the canonical "{entityId}") in the 'routes' option
 * of the #[AdminDashboard] attribute. This tests that:
 *   1) the route generator accepts the "{id}" alias in dashboard-level route configs
 *      (it used to require the literal "{entityId}" placeholder);
 *   2) a custom 'detail' path of "/{id}" is detected as a catch-all route and sorted
 *      last, so it doesn't shadow the routes of the other actions.
 */
#[AdminDashboard(
    routePath: '/id-alias-admin',
    routeName: 'id_alias_admin',
    routes: [
        'detail' => ['routePath' => '/{id}'],
        'edit' => ['routePath' => '/{id}/edit'],
    ],
    allowedControllers: [MultipleRouteCrudController::class],
)]
class IdAliasDashboardController extends AbstractDashboardController
{
}
