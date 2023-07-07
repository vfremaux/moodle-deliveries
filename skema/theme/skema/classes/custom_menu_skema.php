<?php

namespace theme_skema;
defined('MOODLE_INTERNAL') || die();


use core_courseformat\base as course_format;
global $CFG;

require_once($CFG->dirroot .'/course/format/classes/output/section_renderer.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/datalib.php');
require_once($CFG->dirroot.'/course/format/lib.php');
require_once($CFG->libdir . '/behat/lib.php');

/* CLASSE PARA CARREGAR O MENU LATERAL */

class custom_menu_skema {

    /* VALIDA SE USUÁRIO É UM ADMINISTRADOR OU GERENTE*/
    public function is_admin($user){
        global $DB;
        $tipo_usuario = $DB->get_record_sql("
            select 
                    count(*) quant
            from mdl_user u
            where 
                    u.id = ?     
                    and (
                        find_in_set(u.id, (select value from mdl_config where name = 'siteadmins'))
                        or exists (select 1 from mdl_role_assignments r where roleid = 1 and r.userid = u.id)
                    )          
            ",[$user]
        );

        if($tipo_usuario->quant > 0){
            return true;
        }
        else false;
    }

    public function is_professor($usuario,$curso){
        global $DB;

        $validacao = $DB->get_record_sql("
            select 
                count(*) quant
            from mdl_context context 
            join mdl_course course 
                on context.instanceid = course.id	
            join mdl_role_assignments inscricao 
                on context.id = inscricao.contextid
            where 
            inscricao.userid = ?
            and inscricao.roleid in (1,2,9,3)
            and course.id = ?
        ",[$usuario,$curso]);

      if($validacao->quant > 0){
            return true;
      }else{
            return false;
      } 
    }

    public function is_viewprofessor($usuario,$curso){
        global $DB;

        $validacao = $DB->get_record_sql("
            select 
                count(*) quant
            from mdl_context context 
            join mdl_course course 
                on context.instanceid = course.id	
            join mdl_role_assignments inscricao 
                on context.id = inscricao.contextid
            where 
            inscricao.userid = ?
            and inscricao.roleid in (4)
            and course.id = ?
        ",[$usuario,$curso]);

      if($validacao->quant > 0){
            return true;
      }else{
            return false;
      } 
    }

    public function is_student($usuario,$curso){
        global $DB;
        $validacao = $DB->get_record_sql("
            select count(*) quant
            from mdl_context context 
            join mdl_course course 
                on context.instanceid = course.id	
            join mdl_role_assignments inscricao 
                on context.id = inscricao.contextid
            where 
            inscricao.userid = ?
            and inscricao.roleid in (5,6)
            and course.id = ?
        ",[$usuario,$curso]);

      if($validacao->quant > 0){
            return true;
      }else{
            return false;
      } 
    }

    public function is_visible($usuario,$curso){
        global $DB;
        $validacao = $DB->get_record_sql("
            select count(*) quant
            from mdl_context context 
            join mdl_course course 
                on context.instanceid = course.id	
            join mdl_role_assignments inscricao 
                on context.id = inscricao.contextid
            where 
            inscricao.userid = ?
            and course.id = ?
            and course.visible = 1
        ",[$usuario,$curso]);

      if($validacao->quant > 0){
            return true;
      }else{
            return false;
      } 
    }


    /* CARREGA O TIPO DE USUÁRIO */
    public function get_tipo_usuario($user,$course){
        global $DB;
        if($this->is_admin($user)){
            return 0;
        }else{
            $tipo_usuarios = $DB->get_record_sql("
                    select 
                        ra.roleid
                    from mdl_course c 
                    LEFT OUTER JOIN mdl_context cx 
                        ON c.id = cx.instanceid 
                    LEFT OUTER JOIN mdl_role_assignments ra 
                        ON cx.id = ra.contextid 
                        /*AND ra.roleid = '3'*/ 
                    LEFT OUTER JOIN mdl_user u 
                        ON ra.userid = u.id 
                    WHERE 
                    cx.contextlevel = '50'
                    and u.id = ?
                    and c.id = ?
                ",[ 
                    $user,$course
                ]
            );
            return $tipo_usuarios->roleid;
        }        
    }
    /* CARREGA O MENU POR TIPO DE USUÁRIO */
    public function get_menu_lateral($user,$course){
        global $DB;
        global $PAGE;

        $tipo_usuario = $this->get_tipo_usuario($user,$course);
        //administrador e gerente
        if($tipo_usuario == '0' || $tipo_usuario == '1'){
            $menu_curso = $DB->get_records_sql("
                SELECT
                    cu.id course_id,
                    cu.fullname,
                    cu.shortname,
                    cd.value,
                    cx.instanceid,
                    case 
                        when principal.principal = 1 then 'Common Resources'
                        else 
                            case 
                                when course_name.course_name = '' or course_name.course_name is null then cu.fullname
                                else course_name.course_name
                            end
                    end as course_name,
                    principal.principal,
                    cu.visible,
                    cu.category
                FROM mdl_context as cx /*contexto*/
                /* valor campos customizados */
                join mdl_customfield_data as cd 
                    on cd.contextid = cx.id
                /* Campos customizados */
                join mdl_customfield_field as cf 
                    on cd.fieldid = cf.id
                /* CURSO */
                join mdl_course as cu 
                    on cx.instanceid = cu.id
                join (
                    select 
                        cd3.contextid,
                        cd3.value course_name
                    from mdl_customfield_data as cd3
                    join mdl_customfield_field as cf3 
                        on cd3.fieldid = cf3.id
                    WHERE
                        cf3.shortname = 'course_originalname'
                ) course_name
                    on course_name.contextid = cx.id
                join (
                    select 
                        cd4.contextid,
                        cd4.value principal
                    from mdl_customfield_data as cd4
                    join mdl_customfield_field as cf4 
                        on cd4.fieldid = cf4.id
                    WHERE
                        cf4.shortname = 'is_main_skemacourse'
                ) principal
                    on principal.contextid = cx.id
                where
                cf.shortname = 'skemacourse_id'
                and cx.contextlevel = '50'
                /* CARREGA CURSOS COM O MESMO skemacourse_id */
                and exists (
                    select cd2.value
                    from mdl_context as cx2 /*contexto*/
                    /* valor campos customizados */
                    join mdl_customfield_data as cd2
                        on cd2.contextid = cx2.id
                    /* Campos customizados */
                    join mdl_customfield_field as cf2 
                        on cd2.fieldid = cf2.id
                    where
                    cx2.contextlevel = cx.contextlevel
                    and cf2.shortname = 'skemacourse_id'
                    and cx2.contextlevel = '50'
                    and cd2.value = cd.value
                    and cx2.instanceid = ?
                )
                order by 
                principal.principal desc
            ",[$course]);
            $m = [];
            foreach($menu_curso as $menu){
                $baseurl = new \moodle_url(
                    '/course/management.php',
                    array('courseid' => $menu->course_id, 'categoryid' => $menu->category, 'sesskey' => \sesskey())
                );
                $construct = [
                    'course_id' => $menu->course_id,
                    'fullname'  => $menu->fullname,
                    'shortname'  => $menu->shortname,
                    'course_name'  => $menu->course_name,
                    'current' =>   $menu->course_id==$course?'current':null,
                    'course_url' => new \moodle_url('/course/view.php?id='.$menu->course_id),
                    'visible'   => $menu->visible == '1' ? true : false,
                    'professor' => true,
                    'course_hidden' => new \moodle_url($baseurl, array('action' => 'hidecourse')),
                    'course_show' =>   new \moodle_url($baseurl, array('action' => 'showcourse')),     
                ];
                array_push($m,$construct);
            } 
            # array dados
            $menu = [
                'tipo_id' => $tipo_usuario,
                'tipo_desc' => 'professor',
                'professor' => true,
                'aluno'     => false,
                'professor_menu' => null,
                'menu_curso' => $m,
                'mostrar_menu' => true,
            ];
        }else{
            //ALUNO
            if($this->is_student($user,$course)){
                $menu_curso = $DB->get_records_sql("
                    SELECT
                        cu.id course_id,
                        cu.fullname,
                        cu.shortname,
                        cd.value,
                        cx.instanceid,
                        case 
                            when principal.principal = 1 then 'Common Resources'
                            else 
                                case 
                                    when course_name.course_name = '' or course_name.course_name is null then cu.fullname
                                    else course_name.course_name
                                end
                        end as course_name,
                        principal.principal,
                        cu.visible,
                        cu.category
                    FROM mdl_context as cx /*contexto*/
                    /* valor campos customizados */
                    left join mdl_customfield_data as cd 
                        on cd.contextid = cx.id
                    /* Campos customizados */
                    left join mdl_customfield_field as cf 
                        on cd.fieldid = cf.id
                    /* CURSO */
                    left join mdl_course as cu 
                        on cx.instanceid = cu.id
                    /* Carrega os professores dos cursos */
                    left JOIN mdl_role_assignments ra 
                        ON cx.id = ra.contextid
                    /* Carrega usuarios */
                    left JOIN mdl_user us 
                        ON ra.userid = us.id     
                    left join (
                        select 
                            cd3.contextid,
                            cd3.value course_name
                        from mdl_customfield_data as cd3
                        join mdl_customfield_field as cf3 
                            on cd3.fieldid = cf3.id
                        WHERE
                            cf3.shortname = 'course_originalname'
                    ) course_name
                        on course_name.contextid = cx.id
                    left join (
                        select 
                            cd4.contextid,
                            cd4.value principal
                        from mdl_customfield_data as cd4
                        join mdl_customfield_field as cf4 
                            on cd4.fieldid = cf4.id
                        WHERE
                            cf4.shortname = 'is_main_skemacourse'
                    ) principal
                        on principal.contextid = cx.id
                    where
                    cf.shortname = 'skemacourse_id'
                    and cx.contextlevel = '50'
                    /*CODIÇÕES*/
                    and us.id = ? /*USUARIO TELA*/
                    /* CARREGA CURSOS COM O MESMO skemacourse_id */
                    and exists (
                        select cd2.value
                        from mdl_context as cx2 /*contexto*/
                        join mdl_course as cu2 
                            on cx2.instanceid = cu2.id
                        /* valor campos customizados */
                        join mdl_customfield_data as cd2
                            on cd2.contextid = cx2.id
                        /* Campos customizados */
                        join mdl_customfield_field as cf2 
                            on cd2.fieldid = cf2.id
                        where
                        cx2.contextlevel = cx.contextlevel
                        and cf2.shortname = 'skemacourse_id'
                        and cx2.contextlevel = '50'
                        and cd2.value = cd.value
                        and cx2.instanceid = ?
                    )
                    order by 
                    principal.principal desc
                ",[$user,$course]);


            }else{
                //OUTRO
                $menu_curso = $DB->get_records_sql("
                    SELECT
                        cu.id course_id,
                        cu.fullname,
                        cu.shortname,
                        cd.value,
                        cx.instanceid,
                        case 
                            when principal.principal = 1 then 'Common Resources'
                            else 
                                case 
                                    when course_name.course_name = '' or course_name.course_name is null then cu.fullname
                                    else course_name.course_name
                                end
                        end as course_name,
                        principal.principal,
                        cu.visible,
                        cu.category
                    FROM mdl_context as cx /*contexto*/
                    /* valor campos customizados */
                    left join mdl_customfield_data as cd 
                        on cd.contextid = cx.id
                    /* Campos customizados */
                    left join mdl_customfield_field as cf 
                        on cd.fieldid = cf.id
                    /* CURSO */
                    left join mdl_course as cu 
                        on cx.instanceid = cu.id
                    /* Carrega os professores dos cursos */
                    left JOIN mdl_role_assignments ra 
                        ON cx.id = ra.contextid
                    /* Carrega usuarios */
                    left JOIN mdl_user us 
                        ON ra.userid = us.id     
                    left join (
                        select 
                            cd3.contextid,
                            cd3.value course_name
                        from mdl_customfield_data as cd3
                        join mdl_customfield_field as cf3 
                            on cd3.fieldid = cf3.id
                        WHERE
                            cf3.shortname = 'course_originalname'
                    ) course_name
                        on course_name.contextid = cx.id
                    left join (
                        select 
                            cd4.contextid,
                            cd4.value principal
                        from mdl_customfield_data as cd4
                        join mdl_customfield_field as cf4 
                            on cd4.fieldid = cf4.id
                        WHERE
                            cf4.shortname = 'is_main_skemacourse'
                    ) principal
                        on principal.contextid = cx.id
                    where
                    cf.shortname = 'skemacourse_id'
                    and cx.contextlevel = '50'
                    /*CODIÇÕES*/
                    and us.id = ? /*USUARIO TELA*/
                    /* CARREGA CURSOS COM O MESMO skemacourse_id */
                    and exists (
                        select cd2.value
                        from mdl_context as cx2 /*contexto*/
                        join mdl_course as cu2 
                            on cx2.instanceid = cu2.id
                        /* valor campos customizados */
                        join mdl_customfield_data as cd2
                            on cd2.contextid = cx2.id
                        /* Campos customizados */
                        join mdl_customfield_field as cf2 
                            on cd2.fieldid = cf2.id
                        where
                        cx2.contextlevel = cx.contextlevel
                        and cf2.shortname = 'skemacourse_id'
                        and cx2.contextlevel = '50'
                        and cd2.value = cd.value
                        and cx2.instanceid = ?
                    )
                    order by principal.principal desc
                ",[$user,$course]);
            }
                $m = [];
                foreach($menu_curso as $menu){

                    if($this->is_visible($user,$menu->course_id) || $this->is_professor($user,$menu->course_id) || $this->is_viewprofessor($user,$menu->course_id) ){
                        $baseurl = new \moodle_url(
                            '/course/management.php',
                            array('courseid' => $menu->course_id, 'categoryid' => $menu->category, 'sesskey' => \sesskey())
                        );

                        $construct = [
                            'course_id' => $menu->course_id,
                            'fullname'  => $menu->fullname,
                            'shortname'  => $menu->shortname,
                            'course_name'  => $menu->course_name,
                            'current' =>   $menu->course_id==$course?'current':null,
                            'course_url' => new \moodle_url('/course/view.php?id='.$menu->course_id),
                            'visible'   => $menu->visible == '1' ? true : false,  
                            'professor_editor' => $this->is_viewprofessor($user,$menu->course_id),  
                            'professor' => $this->is_professor($user,$menu->course_id),  
                            'course_hidden' => new \moodle_url($baseurl, array('action' => 'hidecourse')),
                            'course_show' =>   new \moodle_url($baseurl, array('action' => 'showcourse')),     
                        ];
                        array_push($m,$construct);
                    }
                } 
                # array dados
                $menu = [
                    'tipo_id' => $tipo_usuario,
                    'tipo_desc' => 'professor',
                    'professor' => true,
                    'aluno'     => false,
                    'professor_menu' => null,
                    'menu_curso' => $m,
                    'mostrar_menu' => count($m) > 1 || $this->is_professor($user,$course) ? true : false,
                ];
        } 
        return $menu;
    }
    
    protected function get_has_restrictions($section,$cursoid): bool {
        global $CFG;

        global $PAGE;global $DB;
        $c = $DB->get_record('course', array('id' => $cursoid));
        $format = course_get_format($c);
        $course = $format->get_course();

        $context = \context_course::instance($course->id);

        // Hidden sections have no restriction indicator displayed.
        if (empty($section->visible) || empty($CFG->enableavailability)) {
            return false;
        }
        // The activity is not visible to the user but it may have some availability information.
        if (!$section->uservisible) {
            return !empty($section->availableinfo);
        }
        
        // Regular users can only see restrictions if apply to them.
        return false;
    }


} 