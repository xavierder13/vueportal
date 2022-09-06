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
import EmployeeLoansIndex from './views/employee/loans/EmployeeLoansIndex.vue';
import EmployeeLoansListView from './views/employee/loans/EmployeeLoansListView.vue';
import EmployeePremiumsIndex from './views/employee/premiums/EmployeePremiumsIndex.vue';
import EmployeePremiumsListView from './views/employee/premiums/EmployeePremiumsListView.vue';
import MyFiles from './views/training_file/MyFiles.vue';
import FilesTutorials from './views/training_file/FilesTutorials.vue';
import TacticalIndex from './views/tactical_requisition/TacticalIndex.vue';
import TacticalCreate from './views/tactical_requisition/TacticalCreate.vue';
import TacticalView from './views/tactical_requisition/TacticalView.vue';
import MarketingEventIndex from './views/tactical_requisition/marketing_event/Index.vue';
import MarketingEventCreate from './views/tactical_requisition/marketing_event/Create.vue';
import MarketingEventEdit from './views/tactical_requisition/marketing_event/Edit.vue';
import AccessChartIndex from './views/access_chart/AccessChartIndex.vue';
import AccessChartCreate from './views/access_chart/AccessChartCreate.vue';
import AccessModuleIndex from './views/access_module/AccessModuleIndex.vue';
import AccessLevel from './views/access_level/AccessLevel.vue';
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
        path: '/employee/loans/list',
        name: 'employee.loans.list',
        component: EmployeeLoansIndex
      },
      {
        path: '/employee/loans/view/list/:branch_id',
        name: 'employee.loans.list.view',
        component: EmployeeLoansListView
      },
      {
        path: '/employee/premiums/list',
        name: 'employee.premiums.list',
        component: EmployeePremiumsIndex
      },
      {
        path: '/employee/premiums/view/list/:branch_id',
        name: 'employee.premiums.list.view',
        component: EmployeePremiumsListView
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
        path: '/tactical_requisition/view/:tactical_requisition_id',
        name: 'tactical.view',
        component: TacticalView
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
        path: '/marketing_event/edit/:marketing_event_id',
        name: 'marketing.event.edit',
        component: MarketingEventEdit
      },
      {
        path: '/access_chart/index',
        name: 'access.chart.index',
        component: AccessChartIndex
      },
      {
        path: '/access_chart/create',
        name: 'access.chart.create',
        component: AccessChartCreate
      },
      {
        path: '/access_module/index',
        name: 'access.module.index',
        component: AccessModuleIndex
      },
      {
        path: '/access_level',
        name: 'access.level.index',
        component: AccessLevel
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