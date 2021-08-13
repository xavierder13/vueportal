import Vue from 'vue';
import Router from 'vue-router';
import Home from './views/Home.vue';
import Login from './auth/Login.vue';
import Dashboard from './views/dashboard/Dashboard.vue';
import UserIndex from './views/user/UserIndex.vue';
import UserCreate from './views/user/UserCreate.vue';
import ProductIndex from './views/inventory/ProductIndex.vue';
import ScanProduct from './views/inventory/ScanProduct.vue';
import InventoryReconciliation from './views/inventory/InventoryReconciliation.vue';
import InventoryReconView from './views/inventory/InventoryReconView.vue';
import BrandIndex from './views/brand/BrandIndex.vue';
import ProductCategoryIndex from './views/product_category/ProductCategoryIndex.vue';
import BranchIndex from './views/branch/BranchIndex.vue';
import EmployeeIndex from './views/employee/EmployeeIndex.vue';
import EmployeeListView from './views/employee/EmployeeListView.vue';
import Permission from './views/permission/PermissionIndex.vue';
import Role from './views/role/RoleIndex.vue';
import ActivityLogs from './views/activity_logs/ActivityLogs.vue';
import PageNotFound from './404/PageNotFound.vue';
import Unauthorize from './401/Unauthorize.vue';

Vue.use(Router);

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    children: [
      {
        path: '/user/index',
        name: 'user.index',
        component: UserIndex
      },
      {
        path: '/user/create',
        name: 'user.create',
        component: UserCreate
      },
      {
        path: '/product/index',
        name: 'product.index',
        component: ProductIndex
      },
      {
        path: '/scan_product',
        name: 'scan.product',
        component: ScanProduct
      },
      {
        path: '/inventory/reconciliation',
        name: 'inventory.reconciliation',
        component: InventoryReconciliation
      },
      {
        path: '/inventory/view/reconciliation/:inventory_recon_id',
        name: 'inventory.reconciliation.view',
        component: InventoryReconView
      },
      {
        path: '/brand/index',
        name: 'brand.index',
        component: BrandIndex
      },
      {
        path: '/product_category/index',
        name: 'product_category.index',
        component: ProductCategoryIndex
      },
      {
        path: '/branch/index',
        name: 'branch.index',
        component: BranchIndex
      },
      {
        path: '/employee/list',
        name: 'employee.list',
        component: EmployeeIndex
      },
      {
        path: '/employee/view/list/:branch_id',
        name: 'employee.list.view',
        component: EmployeeListView
      },
      {
        path: '/permission/index',
        name: 'permission.index',
        component: Permission
      },
      {
        path: '/role/index',
        name: 'role.index',
        component: Role
      },
      {
        path: '/activity_logs',
        name: 'activity_logs',
        component: ActivityLogs
      },
      {
        path: '/unauthorize',
        name: 'unauthorize',
        component: Unauthorize,
      }
    ],
    beforeEnter(to, from, next) {

      if (localStorage.getItem('access_token')) {
        next();
      }
      else {
        next('/login');
      }
    }
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    beforeEnter(to, from, next) {
      
      if (localStorage.getItem('access_token')) {
        next('/');
      }
      else {
        next();
      }
    }
  },
  {
    path: '*',
    name:'not.found',
    component: PageNotFound,
  },
];

const router = new Router({
  routes: routes
});

export default router;