<?php

return array(
  'frontend' =>
  array(
    'user' =>
    array(
      'email_changed_notice' => 'You must confirm your new e-mail address before you can log in again.',
      'profile_updated' => 'Profile successfully updated.',
      'password_updated' => 'Password successfully updated.',
      'change_email_notice' => 'If you change your e-mail you will be logged out until you confirm your new e-mail address.',
    ),
    'test' => 'Test',
    'tests' =>
    array(
      'based_on' =>
      array(
        'permission' => 'Permission Based - ',
        'role' => 'Role Based - ',
      ),
      'js_injected_from_controller' => 'Javascript Injected from a Controller',
      'using_blade_extensions' => 'Using Blade Extensions',
      'using_access_helper' =>
      array(
        'array_permissions' => 'Using Access Helper with Array of Permission Names or ID\'s where the user does have to possess all.',
        'array_permissions_not' => 'Using Access Helper with Array of Permission Names or ID\'s where the user does not have to possess all.',
        'array_roles' => 'Using Access Helper with Array of Role Names or ID\'s where the user does have to possess all.',
        'array_roles_not' => 'Using Access Helper with Array of Role Names or ID\'s where the user does not have to possess all.',
        'permission_id' => 'Using Access Helper with Permission ID',
        'permission_name' => 'Using Access Helper with Permission Name',
        'role_id' => 'Using Access Helper with Role ID',
        'role_name' => 'Using Access Helper with Role Name',
      ),
      'view_console_it_works' => 'View console, you should see \'it works!\' which is coming from FrontendController@index',
      'you_can_see_because' => 'You can see this because you have the role of \':role\'!',
      'you_can_see_because_permission' => 'You can see this because you have the permission of \':permission\'!',
    ),
    'general' =>
    array(
      'joined' => 'Joined',
      'add_to_wishlist' => 'Add To Wishlist',
    ),
    'welcome_to' => 'Welcome to :place',
  ),
  'backend' =>
  array(
    'menu_manager' =>
    array(
      'page' => 'Page',
      'link' => 'Link',
      'title' => 'Menu Manager',
      'edit_menus' => 'Edit Menus',
      'locations' => 'Locations',
      'select_to_edit' => 'Select the menu you want to edit',
      'create_new' => 'Create new menu',
      'or' => 'Or',
      'choose' => 'Choose',
      'welcome' => 'Welcome',
      'url' => 'URL',
      'label' => 'Label',
      'add_to_menu' => 'Add to Menu',
      'screen_reader_text' => 'Press return or enter to expand',
      'categories' => 'Categories',
      'Category' => 'Category',
      'pages' => 'Pages',
      'posts' => 'Posts',
      'post' => 'Post',
      'custom_link' => 'Custom Link',
      'menu_structure' => 'Menu Structure',
      'name' => 'Name',
      'create_menu' => 'Create Menu',
      'save_menu' => 'Save Menu',
      'delete_menu' => 'Delete Menu',
      'sub_menu' => 'Sub-menu',
      'menu_creation' => 'Menu Creation',
      'drag_instruction_1' => 'Place each item in the order you prefer. Click on the arrow to the right of the item to display more configuration options.',
      'drag_instruction_2' => 'Please enter the name and select "Create menu" button',
      'class' => 'Class CSS (optional)',
      'move' => 'Move',
      'move_up' => 'Move up',
      'move_down' => 'Move down',
      'move_right' => 'Move right',
      'move_left' => 'Move left',
      'top' => 'Top',
      'delete' => 'Delete',
      'cancel' => 'Cancel',
      'update_item' => 'Update Item',
      'menu_settings' => 'Menu Settings',
      'auto_add_pages' => 'Auto Add Pages',
      'auto_add_pages_desc' => 'Automatically add new top-level pages to this menu',
      'display' => 'Display',
      'top_menu' => 'Top Menu',
      'footer_menu' => 'Footer Menu',
      'currently' => 'Currently set to',
      'theme_location' => 'Theme Location',
      'save_changes' => 'Save Changes',
      'assigned_menu' => 'Assigned Menu',
      'edit' => 'Edit',
      'select_all' => 'Select All',
    ),
    'general' =>
    array(
      'trainers' => 'Trainers',
      'are_you_sure' => 'Are you sure you want to do this?',
      'app_back_to_list' => 'Back to list',
      'app_save' => 'Save',
      'actions' => 'Actions',
      'app_update' => 'Update',
      'app_restore' => 'Restore',
      'app_permadel' => 'Permanently Delete',
      'all_rights_reserved' => 'All Rights Reserved.',
      'app_add' => 'Add',
      'app_create' => 'Create',
      'app_edit' => 'Edit',
      'app_view' => 'View',
      'app_list' => 'List',
      'app_no_entries_in_table' => 'No entries in table',
      'custom_controller_index' => 'Custom controller index.',
      'app_logout' => 'Logout',
      'app_add_new' => 'Add new',
      'app_add_new_course' => 'Add new Course',
      'app_are_you_sure' => 'Are you sure?',
      'app_assign_trainer' => 'Please asssign trainer for these Course',
      'app_assign_lessons ' => 'Please add lessons',
      'app_dashboard' => 'Dashboard',
      'app_delete' => 'Delete',
      'all' => 'All',
      'trashed' => 'Trashed',
      'boilerplate_link' => 'TadreebLMS',
      'continue' => 'Continue',
      'member_since' => 'Member since',
      'minutes' => ' minutes',
      'search_placeholder' => 'Search...',
      'timeout' => 'You were automatically logged out for security reasons since you had no activity in ',
      'see_all' =>
      array(
        'messages' => 'See all messages',
        'notifications' => 'View all',
        'tasks' => 'View all tasks',
      ),
      'status' =>
      array(
        'online' => 'Online',
        'offline' => 'Offline',
      ),
      'you_have' =>
      array(
        'messages' => '{0} You don\'t have messages|{1} You have 1 message|[2,Inf] You have :number messages',
        'notifications' => '{0} You don\'t have notifications|{1} You have 1 notification|[2,Inf] You have :number notifications',
        'tasks' => '{0} You don\'t have tasks|{1} You have 1 task|[2,Inf] You have :number tasks',
      ),
    ),
    'access' =>
    array(
      'users' =>
      array(
        'if_confirmed_off' => '(If confirmed is off)',
        'delete_user_confirm' => 'Are you sure you want to delete this user permanently? Anywhere in the application that references this user\'s id will most likely error. Proceed at your own risk. This can not be un-done.',
        'no_deactivated' => 'There are no deactivated users.',
        'no_deleted' => 'There are no deleted users.',
        'restore_user_confirm' => 'Restore this user to its original state?',
      ),
    ),
    'dashboard' =>
    array(
      'title' => 'Dashboard',
      'welcome' => 'Welcome',
      'my_courses' => 'My Courses',
      'average-completion-rate' => 'Average Completion Rate',
      'average-assessment-score' => 'Average Assessment Score',
      'Total-Certificates' => 'Total Certificates',
      'Achieved-certificates' => 'Achieved certificates',
      'Completed' => 'Completed',
      'Remaining' => 'Remaining',
      'Filter-Your-Progress' => 'Filter Your Progress',
      'Employee' => 'Select Employee',
      'select-one' => 'Select One',
      'Period' => 'Period',
      'Department' => 'User Group',
      'Course' => 'Select Course',
      'Course-Categeory' => 'Select Category',
      'search' => 'Search',
      'reset' => 'Reset',
      'Active-Users' => 'Active Users',
      'Added-Courses' => 'Added Courses',
      'Assigned-Users' => 'Assigned Users',
      'Assignments' => 'Assignments',
      'Certificate-Issued' => 'Certificate Issued',
      'Recent-Courses' => 'Recent Courses (Last 10 Created)'
    ),
    'search' =>
    array(
      'empty' => 'Please enter a search term.',
      'incomplete' => 'You must write your own search logic for this system.',
      'title' => 'Search Results',
      'results' => 'Search Results for :query',
    ),
    'welcome' => 'Welcome to the Dashboard',
  ),
  'emails' =>
  array(
    'contact' =>
    array(
      'subject' => 'A new :app_name contact form submission!',
      'email_body_title' => 'You have a new contact form request: Below are the details:',
    ),
    'auth' =>
    array(
      'account_confirmed' => 'Your account has been confirmed.',
      'thank_you_for_using_app' => 'Thank you for using our application!',
      'click_to_confirm' => 'Click here to confirm your account:',
      'password_reset_subject' => 'Reset Password',
      'password_cause_of_email' => 'You are receiving this email because we received a password reset request for your account.',
      'password_if_not_requested' => 'If you did not request a password reset, no further action is required.',
      'error' => 'Whoops!',
      'greeting' => 'Hello!',
      'regards' => 'Regards,',
      'trouble_clicking_button' => 'If you’re having trouble clicking the ":action_text" button, copy and paste the URL below into your web browser:',
      'reset_password' => 'Click here to reset your password',
    ),
    'offline_order' =>
    array(
      'subject' => 'Regarding your recent order on :app_name',
    ),
  ),
);
