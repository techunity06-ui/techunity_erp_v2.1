$(document).ready(function(){
//alert(root_domain);
	var datas = '';
	 $.ajax({
			type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "get_resource_schedule_data"},
		success: function(response)
		{
			
			datas = response;
			dataload(datas);
			//alert("test");
		}
	});
	

   
    });
    
    function resourceselect(resource_id)
    {
    	
    	//Loading(true);	
		var datas = '';
	 $.ajax({
			type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "get_resource_schedule_data",resource_id:resource_id},
		success: function(response1)
		{
			
			console.log(response1); return false;
			//Unloading();
			datas1 = response1;
			dataload(datas1);
		}
	});
	}
    
    
    function dataload(resdata)
	{
	//alert("hellllo");
	//console.log(resdata);
        var calendar = $('#calendar').fullCalendar({
            header:{
                left: 'prev,next today',
                center: 'title',
                 minTime: "09:00:00",
   				 maxTime: "17:00:00",
                right: 'agendaWeek,agendaDay,month,year'
            },
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            allDaySlot: false,
           
          events: resdata,   
            eventClick:  function(event, jsEvent, view) {
                endtime = $.fullCalendar.moment(event.end).format('h:mm');
                starttime = $.fullCalendar.moment(event.start).format('dddd, MMMM Do YYYY, h:mm');
               // alert(starttime);
                var mywhen = starttime + ' - ' + endtime;
                $('#modalTitle').html(event.title);
                $('#modalWhen').text(mywhen);
                $('#eventID').val(event.id);
                $('#calendarModal').modal();
            },
            
            //header and other values
            select: function(start, end, jsEvent) {
                endtime = $.fullCalendar.moment(end).format('h:mm');
                starttime = $.fullCalendar.moment(start).format('dddd, MMMM Do YYYY, h:mm');
                var mywhen = starttime + ' - ' + endtime;
                start = moment(start).format();
                end = moment(end).format();
              //  alert(starttime);
                $('#createEventModal #startTime').val(start);
                $('#createEventModal #endTime').val(end);
                $('#createEventModal #when').text(mywhen);
                $('#createEventModal').modal('toggle');
           },
           eventDrop: function(event, delta){
           		
				$.ajax({
				type: "POST",
				url: root_domain+'app/dashboard/',
				data: { mode : "update_resource_schedule_data",start:moment(event.start).format(),end:moment(event.end).format(),id:event.id},

				success: function(response) {
				alert("data updated successfully");
				}
				});
				},
           eventResize: function(event) {
              $.ajax({
				type: "POST",
				url: root_domain+'app/dashboard/',
				data: { mode : "update_resource_schedule_data",start:moment(event.start).format(),end:moment(event.end).format(),id:event.id},

				success: function(response) {
				alert("data updated successfully");
				}
				});
           }
        }); 
               
               
       $('#submitButton').on('click', function(e){
           // We don't want this to act as a link so cancel the link action
           e.preventDefault();
           doSubmit();
       });
       
       $('#deleteButton').on('click', function(e){
           // We don't want this to act as a link so cancel the link action
           e.preventDefault();
           doDelete();
       });
       
       /*function doDelete(){
           $("#calendarModal").modal('hide');
           var eventID = $('#eventID').val();
           $.ajax({
               url: 'index.php',
               data: 'action=delete&id='+eventID,
               type: "POST",
               success: function(json) {
                   if(json == 1)
                        $("#calendar").fullCalendar('removeEvents',eventID);
                   else
                        return false;
                    
                   
               }
           });
       }
       function doSubmit(){
           $("#createEventModal").modal('hide');
           var title = $('#title').val();
           var startTime = $('#startTime').val();
           var endTime = $('#endTime').val();
           
           $.ajax({
               url: 'index.php',
               data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime,
               type: "POST",
               success: function(json) {
                   $("#calendar").fullCalendar('renderEvent',
                   {
                       id: json.id,
                       title: title,
                       start: startTime,
                       end: endTime,
                   },
                   true);
               }
           });
           
       }*/
}