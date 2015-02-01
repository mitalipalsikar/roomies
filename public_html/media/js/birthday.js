$(function()
{

	for(i = new Date().getFullYear(); i>1920; i--)
	{
		$('#byear').append($('<option class="option">').val(i).html(i));
	}

	for(i=1; i<13; i++)
	{
		$('#bmonth').append($('<option class="option"> ').val(i).html(i));
	}
	
	getDays();

	$('#byear, #bmonth').change(function() 
	{
		getDays();
	});
});

function getDays() 
{
	$('#bday').html('');
	$('#bday').append($('<option class="option">').html('Select day'));
	month = $('#bmonth').val();
	year = $('#byear').val();
	days = daysInMonth(month, year);
	
	for(i=1; i<days+1; i++)
	{
		$('#bday').append($('<option class="option">').val(i).html(i));
		document.getElementById('bday').style.visibility = "visible";
	}
}

function daysInMonth(month, year)
{
	return new Date(year,month, 0).getDate();
}

