// Loaded after CoreUI app.js

//Message Notification
setInterval(function()
{

    $.ajax({
        type:"POST",
        url:messageNotificationRoute,
        data:{_token:$('meta[name="csrf-token"]').attr('content')},
        datatype:"html",
        success:function(data)
        {
          if(data.unreadMessageCount > 0){
              $('.unreadMessages').empty();
              $('.mob-notification').removeClass('d-none').html('!');
              $('.unreadMessageCounter').removeClass('d-none').html(data.unreadMessageCount)
              var html = "";
              var host = $(location).attr('protocol')+'//'+$(location).attr('hostname')+'/user/messages/?thread=';
              $(data.threads).each(function (key,value) {
                 html+= '<a class="dropdown-item" href="'+host+value.thread_id+'"> ' +
              '<p class="font-weight-bold mb-0">'+value.title+' <span class="badge badge-success">'+value.unreadMessagesCount+'</span></p>' +
              '<p class="mb-0">'+value.message+'</p>' +
              '</a>';

              });
              $('.unreadMessages').html(html);
          }else{
              $('.unreadMessageCounter').addClass('d-none');
              $('.mob-notification').addClass('d-none')
          }
        }
    });
}, 5000);

//Bell Notification
function fetchBellNotifications() {
    if (typeof bellNotificationRoute === 'undefined') return;

    $.ajax({
        type: "POST",
        url: bellNotificationRoute,
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        datatype: "json",
        success: function(data) {
            if (data.unreadCount > 0) {
                $('.unreadNotificationCounter').removeClass('d-none').html(data.unreadCount);
                var html = "";
                $(data.notifications).each(function(key, notification) {
                    var iconColorClass = 'text-' + notification.icon_color;
                    var linkHref = notification.link ? notification.link : '#';
                    html += '<a class="dropdown-item notification-item py-2" href="' + linkHref + '" data-notification-id="' + notification.id + '">' +
                        '<div class="d-flex align-items-start">' +
                        '<div class="mr-3"><i class="fas ' + notification.icon + ' ' + iconColorClass + '"></i></div>' +
                        '<div class="flex-grow-1">' +
                        '<p class="font-weight-bold mb-1" style="font-size: 13px;">' + notification.title + '</p>' +
                        (notification.message ? '<p class="mb-1 text-muted" style="font-size: 12px;">' + notification.message + '</p>' : '') +
                        '<small class="text-muted">' + notification.time + '</small>' +
                        '</div>' +
                        '</div>' +
                        '</a>';
                });
                $('.unreadNotifications').html(html);
            } else {
                $('.unreadNotificationCounter').addClass('d-none');
                $('.unreadNotifications').html('<p class="mb-0 text-center py-3 text-muted">No new notifications</p>');
            }
        }
    });
}

// Initial fetch and set interval for bell notifications
$(document).ready(function() {
    fetchBellNotifications();
    setInterval(fetchBellNotifications, 10000);

    // Mark notification as read when clicked
    $(document).on('click', '.notification-item', function(e) {
        var notificationId = $(this).data('notification-id');
        if (notificationId && typeof markNotificationReadRoute !== 'undefined') {
            $.ajax({
                type: "POST",
                url: markNotificationReadRoute.replace(':id', notificationId),
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                datatype: "json"
            });
        }
    });

    // Mark all notifications as read
    $(document).on('click', '.mark-all-read-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof markAllNotificationsReadRoute !== 'undefined') {
            $.ajax({
                type: "POST",
                url: markAllNotificationsReadRoute,
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                datatype: "json",
                success: function() {
                    fetchBellNotifications();
                }
            });
        }
    });
});