<header class="header01 isSticky">
    <div class="container largeContainer">
        <div class="row">
            <div class="col-lg-3 col-md-4">
                <div class="logo">
                    <a href="{{ route('website.home') }}">
                        <img src="{{ asset('images/logo2.png') }}" height="100px" width="auto" alt="Lighthouse Academy" />
                    </a>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="topbar">
                    <p>
                        <i class="twi-bolt"></i>Need Help? Call +2348127823406, +2349169801738
                    </p>
                    <div class="tpRight">
                        <a class="lang" href="javascript:void(0);">
                            <i class="twi-globe2"></i>English
                        </a>
                        <div class="tpSocail">
                            <a href="https://facebook.com/lighthouseacademy" target="_blank" rel="noopener">
                                <i class="twi-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/lighthouseacademy" target="_blank" rel="noopener">
                                <i class="twi-twitter"></i>
                            </a>
                            <a href="https://instagram.com/lighthouseacademy" target="_blank" rel="noopener">
                                <i class="twi-instagram"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="navbar01">
                    <nav class="mainMenu">
                        <ul>
                            <li class="{{ request()->routeIs('website.home') ? 'current-menu-item' : '' }}">
                                <a href="{{ route('website.home') }}">Home</a>
                            </li>
                            <li class="{{ request()->routeIs('website.admission*') ? 'current-menu-item' : '' }}">
                                <a href="{{ route('website.admission') }}">Admission</a>
                            </li>
                            <li class="{{ request()->routeIs('website.portfolio*') ? 'current-menu-item' : '' }}">
                                <a href="{{ route('website.portfolio') }}">Portfolio</a>
                            </li>
                            <li class="{{ request()->routeIs('website.about*') ? 'current-menu-item' : '' }}">
                                <a href="{{ route('website.about') }}">About Us</a>
                            </li>
                            <li class="{{ request()->routeIs('website.contact*') ? 'current-menu-item' : '' }}">
                                <a href="{{ route('website.contact') }}">Contact</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="accessNav">
                        <a href="javascript:void(0);" class="menuToggler">
                            <i class="twi-bars1"></i>
                        </a>
                        <a href="https://llacademy.ng/parent-dashboard" target="_blank" class="qu_btn">
                            Student Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@include('website::components.navigation.mobile-menu')