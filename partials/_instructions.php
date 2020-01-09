<h2>Instructions</h2>
<p> Please open a ticket <a href="https://github.com/bmlt-enabled/bmlt-export/issues" target="_top">https://github.com/bmlt-enabled/bmlt-export/issues</a> with problems, questions or comments.</p>
<div id="bmlt_export_accordion">
    <h3 class="help-accordian"><strong>Basic</strong></h3>
    <div>
        <p>[upcoming_meetings root_server=&quot;https://www.domain.org/main_server&quot; services=&quot;12&quot; timezone="America/New_York]</p>
        <p>Multiple service bodies can be added seperated by a comma like so services=&quot;12,14,15&quot;</p>
        <strong>Attributes:</strong> root_server, services, recursive, grace_period, num_results, display_type, timezone, location_text, time_format, weekday_language
        <p><strong>Shortcode parameters can be combined.</strong></p>
    </div>
    <h3 class="help-accordian"><strong>Shortcode Attributes</strong></h3>
    <div>
        <p>The following are needed.</p>
        <p><strong>root_server</strong></p>
        <p><strong>services</strong></p>
        <p>A minimum of root_server, services and timezone attribute are required, which would return all towns for that service body seperated by a comma.</p>
        <p>Ex. [upcoming_meetings root_server=&quot;https://www.domain.org/main_server&quot; services=&quot;50&quot; timezone="America/New_York"]</p>
    </div>
</div>
