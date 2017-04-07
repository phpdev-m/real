<?php

class Fmessages extends Frontuser_Controller
{

	public function __construct ()
	{
		parent::__construct();
        
        $this->load->model('enquire_m');
	}
    
    public function index()
    {
        echo 'index';
    }
    
	public function mymessages()
	{
	    $this->load->model('favorites_m');

        $lang_id = $this->data['lang_id'];
        $this->data['content_language_id'] = $this->data['lang_id'];
        
        // Main page data
        $this->data['page_navigation_title'] = lang_check('Myfavorites');
        $this->data['page_title'] = lang_check('Myfavorites');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

        $user_id = $this->session->userdata('id');
        
        // Fetch all listings
		$this->data['listings'] = $this->enquire_m->get();
        $this->data['all_estates'] = $this->estate_m->get_form_dropdown('address');
        
        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }

        $output = $this->parser->parse($this->data['settings_template'].'/mymessages.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }

	public function edit()
	{
	    $listing_id = $this->uri->segment(4);

        $lang_id = $this->data['lang_id'];
        $this->data['content_language_id'] = $this->data['lang_id'];

        // Main page data
        $this->data['page_navigation_title'] = lang_check('My message');
        $this->data['page_title'] = lang_check('My message');
        $this->data['page_body']  = '';
        $this->data['page_description']  = '';
        $this->data['page_keywords']  = '';

        $user_id = $this->session->userdata('id');

        // Fetch all listings
        $this->data['enquire'] = $this->enquire_m->get($listing_id);
        $this->data['all_estates'] = $this->estate_m->get_form_dropdown('address');

        //Check if user have permision
        $this->load->model('estate_m');
        if($this->estate_m->check_user_permission($this->data['enquire']->property_id,
                                                  $user_id) > 0)
        {
        }
        else
        {
            redirect('fmessages/mymessages/'.$this->data['lang_code'].'#content');
            exit();
        }

        // Set up the form
        $rules = $this->enquire_m->rules_admin;
        $this->form_validation->set_rules($rules);

        // Process the form
        if($this->form_validation->run() == TRUE)
        {
            if($this->config->item('app_type') == 'demo')
            {
                $this->session->set_flashdata('error',
                        lang('Data editing disabled in demo'));
                redirect('fmessages/mymessages/'.$this->data['lang_code'].'#content');
                exit();
            }

            $data = $this->enquire_m->array_from_rules($rules);

            $id = $this->enquire_m->save($data, $listing_id, TRUE);

            $this->session->set_flashdata('message',
                    '<p class="alert alert-success validation">'.lang_check('Changes saved').'</p>');

            if(!empty($id))
            {
                redirect('fmessages/mymessages/'.$this->data['lang_code'].'#content');
            }
            else
            {
                $this->output->enable_profiler(TRUE);
            }
        }

        // Get templates
        $templatesDirectory = opendir(FCPATH.'templates/'.$this->data['settings_template'].'/components');
        // get each template
        $template_prefix = 'page_';
        while($tempFile = readdir($templatesDirectory)) {
            if ($tempFile != "." && $tempFile != ".." && strpos($tempFile, '.php') !== FALSE) {
                if(substr_count($tempFile, $template_prefix) == 0)
                {
                    $template_output = $this->parser->parse($this->data['settings_template'].'/components/'.$tempFile, $this->data, TRUE);
                    //$template_output = str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $template_output);
                    $this->data['template_'.substr($tempFile, 0, -4)] = $template_output;
                }
            }
        }

        $output = $this->parser->parse($this->data['settings_template'].'/editmessage.php', $this->data, TRUE);
        echo str_replace('assets/', base_url('templates/'.$this->data['settings_template']).'/assets/', $output);
    }
    
	public function delete()
	{
        $listing_id = $this->uri->segment(4);
        
        if($this->config->item('app_type') == 'demo')
        {
            $this->session->set_flashdata('error', 
                    lang('Data editing disabled in demo'));
            redirect('fmessages/mymessages/'.$this->data['lang_code'].'#content');
            exit();
        }
        
        $this->enquire_m->delete($listing_id);
        redirect('fmessages/mymessages/'.$this->data['lang_code'].'#content');
    }
    

}