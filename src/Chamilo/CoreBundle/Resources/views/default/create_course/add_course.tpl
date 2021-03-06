{% extends "@template_style/layout/layout_1_col.tpl" %}

{% block content %}

{{ just_created_link }}
<h3>{{ 'JustCreated'|trans }} {{ course_title }}</h3>
<hr />
<h3>{{ 'ThingsToDo'|trans }}</h3>
<br />

<div id="course_thing_to_do" class="row">
    <div class="col-md-3">
        <div class="thumbnail">
            <img src="{{ _p.web_img }}icons/64/home.png"/>
            <div class="caption">
                <a href="{{ course_url }}" class="btn">
                    {{'CourseHomepage'|trans}}
                </a>
            </div>
        </div>
    </div>

    {% if ("allow_user_course_subscription_by_course_admin" | get_setting) == 'true' or _u.is_admin == 1 %}
    <div class="col-md-3">
        <div class="thumbnail">
        <img src="{{ _p.web_img }}icons/64/user.png"/>
            <div class="caption">
            <a href="{{ _p.web_main }}user/subscribe_user.php?cidReq={{ course_id }}" class="btn">
                {{ 'SubscribeUserToCourse'|trans }}
            </a>
            </div>
        </div>
    </div>
    {% endif %}

    <div class="col-md-3">
        <div class="thumbnail">
        <img src="{{ _p.web_img }}icons/64/info.png"/>
            <div class="caption">
            <a href="{{ _p.web_main }}course_description/?cidReq={{ course_id }}" class="btn">
                {{'AddCourseDescription'|trans}}
            </a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="thumbnail">
        <img src="{{ _p.web_img }}icons/64/reference.png"/>
            <div class="caption">
            <a href="{{ _p.web_main }}course_info/infocours.php?cidReq={{ course_id }}" class="btn">
                {{ 'ModifInfo'|trans }}
            </a>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
{% endblock %}
