import Vue from 'vue';
import Router from 'vue-router';
import Home from './views/Home.vue';
import Login from './auth/Login.vue';
import Dashboard from './views/dashboard/Dashboard.vue';
import UserIndex from './views/user/UserIndex.vue';
import UserCreate from './views/user/UserCreate.vue';
import UserProfile from './views/user/UserProfile.vue';
import ProductIndex from './views/inventory/ProductIndex.vue';
import ScanProduct from './views/inventory/ScanProduct.vue';
import InventoryReconciliation from './views/inventory/InventoryReconciliation.vue';
import InventoryDiscrepancy from './views/inventory/InventoryDiscrepancy.vue';
import InventoryBreakdown from './views/inventory/InventoryBreakdown.vue';
import BrandIndex from './views/brand/BrandIndex.vue';
import PositionIndex from './views/position/PositionIndex.vue';
import DepartmentIndex from './views/department/DepartmentIndex.vue';
import ProductCategoryIndex from './views/product_category/ProductCategoryIndex.vue';
import ProductModelIndex from './views/product_model/ProductModelIndex.vue';
import BranchIndex from './views/branch/BranchIndex.vue';
import CompanyIndex from './views/company/CompanyIndex.vue';
import EmployeeIndex from './views/employee/EmployeeIndex.vue';
import EmployeeListView from './views/employee/EmployeeListView.vue';
import MyFiles from './views/training_file/MyFiles.vue';
import FilesTutorials from './views/training_file/FilesTutorials.vue';
import TacticalIndex from './views/tactical_requisition/TacticalIndex.vue';
import TacticalCreate from './views/tactical_requisition/TacticalCreate.vue';
import MarketingEventIndex from './views/tactical_requisition/marketing_event/Index.vue';
import MarketingEventCreate from './views/tactical_requisition/marketing_event/Create.vue';
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
        path: '/user/profile',
        name: 'user.profile',
        component: UserProfile
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
        path: '/inventory/discrepancy/reconciliation/:inventory_recon_id',
        name: 'inventory.reconciliation.discrepancy',
        component: InventoryDiscrepancy
      },
      {
        path: '/inventory/breakdown/reconciliation/:inventory_recon_id',
        name: 'inventory.reconciliation.breakdown',
        component: InventoryBreakdown
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
        path: '/product_model/index',
        name: 'product_model.index',
        component: ProductModelIndex
      },
      {
        path: '/branch/index',
        name: 'branch.index',
        component: BranchIndex
      },
      {
        path: '/company/index',
        name: 'company.index',
        component: CompanyIndex
      },
      {
        path: '/position/index',
        name: 'position.index',
        component: PositionIndex
      },
      {
        path: '/department/index',
        name: 'department.index',
        component: DepartmentIndex
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
        path: '/training/my_files',
        name: 'training.my_files',
        component: MyFiles
      },
      {
        path: '/training/files_tutorials',
        name: 'training.files_tutorials',
        component: FilesTutorials
      },
      {
        path: '/tactical_requisition/index',
        name: 'tactical.index',
        component: TacticalIndex
      },
      {
        path: '/tactical_requisition/create',
        name: 'tactical.create',
        component: TacticalCreate
      },
      {
        path: '/marketing_event/index',
        name: 'marketing.event.index',
        component: MarketingEventIndex
      },
      {
        path: '/marketing_event/create',
        name: 'marketing.event.create',
        component: MarketingEventCreate
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