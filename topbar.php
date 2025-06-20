 <!--Topbar -->
 <div class="topbar transition">
     <div class="bars">
         <button type="button" class="btn transition" id="sidebar-toggle">
             <i class="las la-bars"></i>
         </button>
     </div>
     <div class="menu">

         <ul>

             <li>
                 <div class="theme-switch-wrapper">
                     <label class="theme-switch" for="checkbox">
                         <input type="checkbox" id="checkbox" title="Dark Or White" />
                         <div class="slider round"></div>
                     </label>
                 </div>
             </li>

             <li>
                 <a href="notifications.html" class="transition">
                     <i class="las la-bell"></i>
                     <span class="badge badge-danger notif">5</span>
                 </a>
             </li>

             <li>
                 <div class="dropdown">
                     <div class="dropdown-toggle" id="dropdownProfile" data-toggle="dropdown" aria-haspopup="true"
                         aria-expanded="false">
                         <img src="assets/images/avatar/avatar-2.png" alt="Profile">
                     </div>
                     <div class="dropdown-menu" aria-labelledby="dropdownProfile">

                         <a class="dropdown-item" href="profile.html">
                             <i class="las la-user mr-2"></i> My Profile
                         </a>

                         <a class="dropdown-item" href="activity-log.html">
                             <i class="las la-list-alt mr-2"></i> Activity Log
                         </a>

                         <div class="dropdown-divider"></div>
                         <a class="dropdown-item" href="signin.html">
                             <i class="las la-sign-out-alt mr-2"></i> Sign Out
                         </a>
                     </div>
                 </div>
             </li>
         </ul>
     </div>
 </div>