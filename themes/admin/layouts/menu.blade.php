<!-- Main navigation -->
<div class="card card-sidebar-mobile">
    <ul class="nav nav-sidebar" data-nav-type="accordion">

        <!-- Main -->
        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">Main</div>
            <i class="icon-menu" title="Main"></i></li>
        @if(user_can('access dashboard'))
            <li class="nav-item">
                <a href="{{url('admin')}}"
                   class="nav-link {!! request()->is('admin')?' active':'' !!}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a href="{{url('admin/pos')}}" class="nav-link {!! request()->is('admin/pos*')?' active':'' !!}">
                <i class="icon-cart-add"></i>
                <span>Sale</span>
            </a>
        </li>

        @if(user_can('access categories'))
            <li class="nav-item">
                <a href="{{url('admin/categories')}}"
                   class="nav-link {!! request()->is('admin/categories*')?' active':'' !!}">
                    <i class="icon-list"></i>
                    <span>Categories</span>
                </a>
            </li>
        @endif
        @if(user_can('access products'))
            <li class="nav-item">
                <a href="{{url('admin/products')}}"
                   class="nav-link {!! request()->is('admin/product*')?' active':'' !!}">
                    <i class="icon-album"></i>
                    <span>Products</span>
                </a>
            </li>
        @endif
        @if(user_can('access vendors'))
            <li class="nav-item">
                <a href="{{url('admin/vendors')}}" class="nav-link {!! request()->is('admin/vendors')?' active':'' !!}">
                    <i class="icon-users"></i>
                    <span>Vendors</span>
                </a>
            </li>
        @endif
        @if(user_can('access customers'))
            <li class="nav-item">
                <a href="{{url('admin/customers')}}"
                   class="nav-link {!! request()->is('admin/customers')?' active':'' !!}">
                    <i class="icon-people"></i>
                    <span>Customers</span>
                </a>
            </li>

        @endif
        @if(user_can('access invoices'))
            <li
                class="nav-item nav-item-submenu">
                <a
                    class="nav-link {!! request()->is('admin/customer-invoice*') || request()->is('admin/vendor-invoice*')?' active':'' !!}">
                    <i
                        class="icon-copy"></i>
                    <span>Invoices</span></a>

                <ul class="nav nav-group-sub"
                    data-submenu-title="Invoices" style="display: none">
                    <li>
                        <a href="{{url('admin/customer-invoices')}}"
                           class="nav-link {!! request()->is('admin/customer-invoice*')?' active':'' !!}">
                            <i class=" icon-cash"></i>
                            <span>Customers</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{url('admin/vendor-invoices')}}"
                           class="nav-link {!! request()->is('admin/vendor-invoice*')?' active':'' !!}">
                            <i class=" icon-cash"></i>
                            <span>Vendors</span>
                        </a>
                    </li>


                </ul>
            </li>
        @endif
        @if(user_can('access banks'))
            <li class="nav-item">
                <a href="{{url('admin/bank')}}" class="nav-link {!! request()->is('admin/bank*')?' active':'' !!}">
                    <i class="fas fa-building"></i>
                    <span>Banks</span>
                </a>
            </li>

        @endif
        @if(user_can('access accounts'))
            <li class="nav-item">
                <a href="{{url('admin/accounts')}}"
                   class="nav-link {!! request()->is('admin/account*')?' active':'' !!}">
                    <i class="icon-credit-card"></i>
                    <span>Credit / Debit</span>
                </a>
            </li>
        @endif
        @if(user_can('access reports'))
            <li
                class="nav-item nav-item-submenu">
                <a class="nav-link {!! request()->is('admin/report*')?' active':'' !!}"> <i
                        class="icon-chart"></i>
                    <span>Reports</span></a>

                <ul class="nav nav-group-sub"
                    data-submenu-title="Credit Book<" style="display: none">
                    @if(user_can('access stocks report'))
                        <li>
                            <a href="{{url('admin/reports/available-stock')}}"
                               class="nav-link {!! request()->is('admin/reports/available-stock*')?' active':'' !!}">
                                <i class="fas fa-list"></i>
                                <span>Available / Sold Stock</span>
                            </a>
                        </li>
                    @endif
                    @if(user_can('access stock invoices report'))
                        <li>
                            <a href="{{url('admin/reports/stock')}}"
                               class="nav-link {!! request()->is('admin/reports/stock*')?' active':'' !!}">
                                <i class="fas fa-list"></i>
                                <span>Stock</span>
                            </a>
                        </li>
                    @endif
                    @if(user_can('access inventory report'))
                        <li>
                            <a href="{{url('admin/reports/inventory')}}"
                               class="nav-link {!! request()->is('admin/reports/inventory*')?' active':'' !!}">
                                <i class="fas fa-list"></i>
                                <span>Inventory Log</span>
                            </a>
                        </li>
                    @endif
                    @if(user_can('access orders report'))
                        <li>
                            <a href="{{url('admin/reports/order')}}"
                               class="nav-link {!! request()->is('admin/reports/order*')?' active':'' !!}">
                                <i class="fas fa-list"></i>
                                <span>Order</span>
                            </a>
                        </li>
                    @endif
                    @if(user_can('access profit report'))
                        <li>
                            <a href="{{url('admin/reports/order-items')}}"
                               class="nav-link {!! request()->is('admin/reports/order-item*')?' active':'' !!}">
                                <i class="fas fa-wallet"></i>
                                <span>Margin / Profit</span>
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{url('admin/reports/product-sold')}}"
                           class="nav-link {!! request()->is('admin/reports/product-sold*')?' active':'' !!}">
                            <i class="fas fa-wallet"></i>
                            <span>Product Sold</span>
                        </a>
                    </li>

                </ul>
            </li>
        @endif
        @if(user_can('access roles'))
            <li class="nav-item">
                <a href="{{url('admin/roles')}}"
                   class="nav-link {!! request()->is('admin/reports/role*')?' active':'' !!}">
                    <i class="icon-menu3"></i>
                    <span>Roles</span>
                </a>
            </li>
        @endif
        @if(user_can('access users'))
            <li class="nav-item">
                <a href="{{url('admin/users')}}"
                   class="nav-link {!! request()->is('admin/reports/user*')?' active':'' !!}">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
        @endif
        @if(user_can('access settings'))
            <li class="nav-item">
                <a href="{{route('setup')}}"
                   class="nav-link {!! request()->is('admin/reports/setup*')?' active':'' !!}">
                    <i class="fas fa-cogs"></i>
                    <span>Settings</span>
                </a>
            </li>
        @endif
        {{--		<li
                    class="nav-item nav-item-submenu">
                    <a
                        class="nav-link {!! request()->is('admin/sms*') || request()->is('admin/log*')?' active':'' !!}">
                        <i
                            class="icon-copy"></i>
                        <span>SMS / Logs</span></a>

                    <ul class="nav nav-group-sub"
                                    data-submenu-title="SMS/Logs" style="display: none">
                        <li class="nav-item">
                            <a href="{{route("admin.sms.file")}}" class="nav-link {!! request()->is('admin/sms/file')?' active':'' !!}">
                                <i class="fas fa-envelope"></i>
                                <span>Send SMS</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{route("admin.numbers.list")}}" class="nav-link {!! request()->is('admin/sms')?' active':'' !!}">
                                <i class="fas fa-archive"></i>
                                <span>SMS History</span>
                            </a>
                        </li>


                        <li class="nav-item">
                            <a href="{{route("admin.numbers.emails")}}" class="nav-link {!! request()->is('admin/emails')?' active':'' !!}">
                                <i class="fas fa-archive"></i>
                                <span>Email History</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{route("admin.logs")}}" class="nav-link {!! request()->is('admin/logs')?' active':'' !!}">
                                <i class="fas fa-archive"></i>
                                <span>Logs History</span>
                            </a>
                        </li>


                    </ul>
                </li>--}}


    </ul>
</div>
<!-- /main navigation -->
