 <?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends CI_Controller {
public function __construct()
{
	parent::__construct();
	if(!$userid = $this->session->userdata('admin_id')){
		redirect(base_url('login'));
	}
	date_default_timezone_set('Asia/Kolkata');
	$militime =round(microtime(true) * 1000);
	$datetime =date('Y-m-d h:i:s');
	define('militime', $militime);
	define('datetime', $datetime);

	/*cache control*/
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->output->set_header('Pragma: no-cache');
		
}
	
public function index()
{ 
	$data['blog_data'] = $this->common_model->getData('blog',array('user_id'=>0,'admin_status'=>1,'delete_status'=>0),'blog_id','DESC');

	$this->load->view('admin/blog/blog_detail',$data);	
}

public function add_blog()
{ 
	if($this->input->server('REQUEST_METHOD') === 'POST')
	{ 
		  	$this->form_validation->set_rules('title', 'Title', 'required');
			$this->form_validation->set_rules('description', 'Description', 'required');

			if($this->form_validation->run() == TRUE)
			{ 
	  			    if(isset($_FILES['image']['name'][0]) && $_FILES['image']['name'][0] != '')
	        		{  
			            $files = $_FILES;	
			        	$filesCount1 = count($_FILES['image']['name']);
	        	       
	        	            $_FILES['image']['name'] =  $files['image']['name'];
	        	            $_FILES['image']['type'] =   $files['image']['type'];
			                $_FILES['image']['tmp_name'] =  $files['image']['tmp_name'];
			                $_FILES['image']['error'] =  $files['image']['error'];
			                $_FILES['image']['size'] =  $files['image']['size'];

			                 $date = date("ymdhis"); 	
	        	             $uploadPath = 'uploads/blog_image/';
	                         $config['upload_path'] = $uploadPath;
	                         $config['allowed_types'] = 'jpg|png|jpeg';
	                         $config['max_size']      = 500; 
					         $config['max_width'] = '700';
					         $config['max_height'] = '500';

		                        $subFileName = explode('.',$_FILES['image']['name']);
		                        $ExtFileName = end($subFileName);

                      	    	$config['file_name'] = md5($date.$_FILES['image']['name']).'.'.$ExtFileName;

                            	$fileName = $config['file_name'];
                           		
	                       		$this->load->library('upload', $config);
	                       		$this->upload->initialize($config);

                       			if($this->upload->do_upload('image'))
                           		{
	                        	  	$fileData = $this->upload->data();
	                              	$uploadData['file_name'] = $fileData['file_name'];
        	        				$image = $fileName; 
                            	}
                            	else
                            	{
                            		$this->data['err']= $this->upload->display_errors();
					 				$this->session->set_flashdata('image_error', $this->data['err']);
				 	 				redirect('blog/add_blog');
				 	 				exit;
                            	}
	              	}
          
          			$blog = array(
					'title' =>$this->input->post('title'),
					'description' =>$this->input->post('description'),
					'admin_status'=>1,
					'publish_date'=>date('Y-m-d H:i:s'),
					'create_at' =>militime,
					'update_at'=>militime
					
					);

					$insert_id = $this->common_model->common_insert('blog',$blog);

	        		if($insert_id)
	        		{
	        			$blog_image = array('blog_id'=>$insert_id,'image'=>$uploadData['file_name'],'create_at'=>militime,'update_at'=>militime);
            	       	$insert = $this->common_model->common_insert('blog_image',$blog_image);	

		                $title = $this->input->post('title');

		                $user_data = $this->common_model->getDataField('device_token,user_id','users',array());

                    	$gcmRegIds = array();
                		$i = 0;
                		foreach($user_data as $user_device_token)
                		{
                		  $i++;
                          $gcmRegIds[floor($i/1000)][] = $user_device_token->device_token;
                          $userid_arr[] = $user_device_token->user_id;
                		}
                            $msg = $title.' '.'Blog Added';
                		    $pushMessage=array("title" =>$title,"user_id"=>'','section_id'=>$insert_id,"message" =>$msg,'image'=>base_url().'uploads/blog_image/'.$image,"type"=>'9',"currenttime"=>militime);
                       
                			if(isset($gcmRegIds)) 
                			{  
		                    	$message = $pushMessage;
		                    	$pushStatus = array();
	                    
	                    		foreach($gcmRegIds as $val){ $pushStatus[] = $this->common_model->sendNotification($val, $message);
                     			}
                     			    $user_id_in_comma = implode(",",$userid_arr);
                      				$insertnoti = $this->common_model->common_insert('notification',array('sender_id'=>'0','receiver_id'=>$user_id_in_comma,'section_id'=>$insert_id,'type'=>'9','title'=>$title,'msg'=>$msg,'image'=>$image,'create_at'=>militime,'update_at'=>militime));

                 			}		


		              	$this->session->set_flashdata('success', 'Blog Inserted Successfully.');
					  	redirect('blog');
	        		}
	  			
			}
	} 
          	$this->load->view('admin/blog/add_blog');
}

public function edit($blog_id=false)
{ 
	$data['blog_data'] = $this->common_model->common_getRow('blog',array('blog_id'=>$blog_id));
	$data['blog_image'] = $this->common_model->common_getRow('blog_image',array('blog_id'=>$blog_id));

	if($this->input->server('REQUEST_METHOD') === 'POST')
	{ 
		  	$this->form_validation->set_rules('title', 'Title', 'required');
			$this->form_validation->set_rules('description', 'Description', 'required');

			if($this->form_validation->run() == TRUE)
			{ 

	  				if(isset($_FILES['image']['name'][0]) && $_FILES['image']['name'][0] != '')
		        	{  
				         $files = $_FILES;	
				        
	        	            $_FILES['image']['name'] =  $files['image']['name'];
	        	            $_FILES['image']['type'] =   $files['image']['type'];
			                $_FILES['image']['tmp_name'] = $files['image']['tmp_name'];
			                $_FILES['image']['error'] =  $files['image']['error'];
			                $_FILES['image']['size'] =  $files['image']['size'];

			                 $date = date("ymdhis"); 	
	        	             $uploadPath = 'uploads/blog_image/';
	                         $config['upload_path'] = $uploadPath;
	                         $config['allowed_types'] = 'jpg|png|jpeg';
	                         $config['max_size']      = 500; 
					         $config['max_width'] = '700';
					         $config['max_height'] = '500';

			                        $subFileName = explode('.',$_FILES['image']['name']);
			                        $ExtFileName = end($subFileName);

	                      	    	$config['file_name'] = md5($date.$_FILES['image']['name']).'.'.$ExtFileName;

	                            	$fileName = $config['file_name'];
	                           		
		                       		$this->load->library('upload', $config);
		                       		$this->upload->initialize($config);

	                       			if($this->upload->do_upload('image'))
	                           		{
		                        	  	$fileData = $this->upload->data();
		                              	$uploadData['file_name'] = $fileData['file_name'];
		                              	$image = $uploadData['file_name'];
	                            	}
	                            	else
	                            	{
	                            		$this->data['err']= $this->upload->display_errors();
					 				    $this->session->set_flashdata('image_error', $this->data['err']);
					 	 				redirect('blog/edit/'.$blog_id);
					 	 				exit;
	                            	}	
		                            
			        }
			        else
			        {
			        	if(!empty($data['blog_image']->image))
			        	{
			        		$image =  $data['blog_image']->image;
			        	}
			        	else
			        	{
			        		$image = '';
			        	}	
			        }

			        $updateimage = $this->common_model->updateData('blog_image',array('image'=>$image),array('blog_id'=>$blog_id));
          
          			$blog = array(
					'title' =>$this->input->post('title'),
					'description' =>$this->input->post('description'),
					'publish_date'=>Date('Y-m-d H:i:s'),
					'create_at'=>militime,
					'update_at'=>militime
					);

          		    $update = $this->common_model->updateData('blog',$blog,array('blog_id'=>$blog_id));
        			if($update)
        			{
	              		$this->session->set_flashdata('success', 'Blog Updated Successfully.');
				  		redirect('blog');
        			}
	  			
			}
	} 
          	$this->load->view('admin/blog/edit_blog',$data);

}
//All details of Article
public function detail($blog_id=false)
{
   $data['blog_data'] = $this->common_model->common_getRow('blog',array('blog_id'=>$blog_id)); 

   $data['blog_image'] = $this->common_model->common_getRow('blog_image',array('blog_id'=>$blog_id)); 
   
   $this->load->view('admin/blog/details',$data);
}

public function delete($blog_id=false)
{  
	$blog_id = $this->input->post('blog_id');

	$delete = $this->db->query("DELETE FROM `blog` WHERE `blog_id` IN ($blog_id)");

	$delete1 = $this->db->query("DELETE FROM `blog_image` WHERE `blog_id` IN ($blog_id)");

	$delete2 = $this->db->query("DELETE FROM `comment` WHERE `project_id` IN ($blog_id) AND `type`='blog'");
   
	$delete3 = $this->db->query("DELETE FROM `notification` WHERE `section_id` IN ($blog_id) AND `type` IN (9,10,11)");
				
	$delete4 = 	$this->db->query("DELETE FROM `Like_Unlike` WHERE `section_id` IN ($blog_id) AND `type`= 'blog'");
					
	echo $blog_id;exit;			
}

public function archived_blog()
{ 
	$data['blog_data'] = $this->common_model->getData('blog',array('admin_status'=>2),'blog_id','DESC');

	$this->load->view('admin/blog/archive_blog',$data);	
}

public function blog_status()
{
   $status = $this->input->post('status');
   $blog_id = $this->input->post('blog_id');
 
   	  $status_data = array('admin_status'=>$status,'publish_date'=>date('Y-m-d H:i:s'));
   	  $status_update = $this->common_model->updateData('blog',$status_data,array('blog_id'=>$blog_id));
   	 	
   	  if($status_update)
   	  {
   	  	    if($status == 1)
   	  		{
   	  	 		$msg = "Your Blog has been Verified by Admin."; 
   	  	 		$title = 'Verified';
   	  		}
   	  		else if($status == 2)
   	  		{
            	$msg = "Your Blog has been Rejected by Admin."; 
            	$title = 'Rejected';
   	  		}	

   	  	    $get_user_id = $this->common_model->common_getRow('blog',array('blog_id'=>$blog_id));

   	  	    if(!empty($get_user_id))
   	  	    { 
	        	    $user_id = $get_user_id->user_id;

	            	$user_devicetoken = $this->common_model->common_getRow('users',array('user_id'=>$user_id));

	            	if(!empty($user_devicetoken->device_token))
	            	{
	            		 $message = array("title"=>$title,"type"=>11,"message" =>$msg,"image"=>'',"currenttime"=>militime);

	                     $this->common_model->sendPushNotification($user_devicetoken->device_token,$message);
	            	}	

	            $insertnoti = $this->common_model->common_insert('notification',array('sender_id'=>'0','receiver_id'=>$user_id,'type'=>'11','title'=>$title,'msg'=>$msg,'create_at'=>militime,'update_at'=>militime));
	            echo '1000'; exit;
   	  	   }
   	  	   else
   	  	   {
   	  		 echo '1000';exit;
   	  	   }	
   	  }

}

public function user_blog()
{
	$data['blog_data'] = $this->db->query("SELECT * FROM `blog` WHERE `user_id` != 0 AND `delete_status` = 0 AND `admin_status` IN(0,1) ")->result();

	$this->load->view('admin/blog/blog_detail',$data);
}

public function remove_img()
{	
	$image_id =  $this->input->post('image_id');

	$delete = $this->common_model->deleteData('blog_image',array('image_id'=>$image_id));

	if($delete)
	{
		echo "1000"; exit;
	}

}

public function  reject_blog()
{  
	$blog_id = $this->input->post('blog_id');
	$status_update = $this->db->query("UPDATE `blog` SET `admin_status` = 2 WHERE `blog_id` IN($blog_id)");
	if($status_update)
	{
		echo $blog_id;
	}
}

public function  active_blog1()
{  
	$blog_id = $this->input->post('blog_id');
	$status_update = $this->db->query("UPDATE `blog` SET `admin_status` = 1 WHERE `blog_id` IN($blog_id)");
	if($status_update)
	{
		echo $blog_id;
	}
}

public function active_blog()
{
	$blog_id = $this->input->post('blog_id');
    $blog_arr = explode(",",$blog_id);

    for($j=0;$j<count($blog_arr);$j++)
    {
        $already_active = $this->common_model->common_getRow('blog',array('blog_id'=>$blog_arr[$j]));

        if($already_active->admin_status == 1)
        {
        	continue;
        }
        else
        { 
            $status_update = $this->common_model->updateData('blog',array('admin_status'=>1,'publish_date'=>date('Y-m-d H:i:s')),array('blog_id'=>$blog_arr[$j]));

            if($status_update)
            {
            	$title = $already_active->title;
            	$user_id = $already_active->user_id;

                $get_image = $this->common_model->common_getRow('blog_image',array('blog_id'=>$blog_arr[$j]));

            	$image = $get_image->image;

            	$msg = "Your '".$title."' Blog has been Verified by Admin."; 

            	$user_devicetoken = $this->common_model->common_getRow('users',array('user_id'=>$user_id));

            	if(!empty($user_devicetoken->device_token))
            	{
            		$message1 = array("title"=>$title,"user_id"=>'',"type"=>11,'section_id'=>$blog_arr[$j],"message" =>$msg,"image" =>base_url().'uploads/blog_image'.$image,"currenttime"=>militime);

                     $this->common_model->sendPushNotification($user_devicetoken->device_token,$message1);
            	}

	            $insertnoti = $this->common_model->common_insert('notification',array('sender_id'=>'0','receiver_id'=>$user_id,'user_id'=>$user_id,'section_id'=>$blog_arr[$j],'type'=>'11','title'=>$title,'msg'=>$msg,'image'=>$image,'create_at'=>militime,'update_at'=>militime));
	            if($insertnoti)
	            {
                    $user_data = $this->common_model->getDataField('device_token,user_id','users',array());

                    	$gcmRegIds = array();
                		$i = 0;
                		foreach($user_data as $user_info)
                		{
                		  $i++;
                          if($user_info->device_token != $user_devicetoken->device_token)
                		  {
                		  	 $gcmRegIds[floor($i/1000)][] = $user_info->device_token;
                             $userid_arr[] = $user_info->user_id;
                		  }

                		}
                           $msg1 = $title.' '.'Blog Added';
                		   $pushMessage=array("title" =>$title,"user_id"=>'','section_id'=>$blog_arr[$j],"message" => $msg1,'image'=>base_url().'uploads/blog_image/'.$image,"type"=>'9',"currenttime"=>militime);
                       
                			if(isset($gcmRegIds)) 
                			{  
		                    	$message = $pushMessage;
		                    	$pushStatus = array();
	                    
	                    		foreach($gcmRegIds as $val){ $pushStatus[] = $this->common_model->sendNotification($val, $message);
                     			}
                     		$user_id_in_comma = implode(",",$userid_arr);	

                      $insertnotification = $this->common_model->common_insert('notification',array('sender_id'=>'0','receiver_id'=>$user_id_in_comma,'section_id'=>$blog_arr[$j],'type'=>'9','title'=>$title,'msg'=>$msg1,'image'=>$image,'create_at'=>militime,'update_at'=>militime));

                 			}

	            }	

            }	

        }	
    }
     echo $blog_id;	
}



public function project_status()
{
   $status = $this->input->post('status');
   $project_id = $this->input->post('project_id'); 
 
   	  $status_data = array('admin_status'=>$status);
   	  $status_update = $this->common_model->updateData('project',$status_data,array('project_id'=>$project_id));

   	  if($status_update)
   	  {
   	  	    if($status == 1)
   	  		{
   	  	 		$msg = "Your Project has been Verified by Admin."; 
   	  	 		$title = 'Verified';
   	  		}
   	  		else if($status == 2)
   	  		{
            	$msg = "Your Project has been Rejected by Admin."; 
            	$title = 'Rejected';
   	  		}	

   	  	    $get_user_id = $this->common_model->common_getRow('project',array('project_id'=>$project_id));

   	  	    if(!empty($get_user_id))
   	  	    { 
	        	    $user_id = $get_user_id->user_id;

	            	$user_devicetoken = $this->common_model->common_getRow('users',array('user_id'=>$user_id));

	            	if(!empty($user_devicetoken->device_token))
	            	{
	            		 $message = array("title"=>$title,"type"=>8,"message" =>$msg,"image"=>'',"currenttime"=>militime);

	                     $this->common_model->sendPushNotification($user_devicetoken->device_token,$message);
	            	}	

	            $insertnoti = $this->common_model->common_insert('notification',array('sender_id'=>'0','receiver_id'=>$user_id,'type'=>'8','title'=>$title,'msg'=>$msg,'create_at'=>militime,'update_at'=>militime));
	            echo '1000'; exit;
   	  	   }
   	  	   else
   	  	   {
   	  		 echo '1000';exit;
   	  	   }	
   	  }	
}

public function user_comment($blog_id = false)
{
   $arr = array();	
   $data = '';
   $comment =  $this->db->query("SELECT * FROM comment WHERE `project_id`= $blog_id AND `type`= 'blog' ORDER BY `comment_id` DESC")->result();

    if(!empty($comment))
    {
    	foreach($comment as $feed)
    	{
			$userdata = $this->common_model->common_getRow('users',array('user_id'=>$feed->user_id));

			if(!empty($userdata))
			{
				$useraname = $userdata->username.' '.$userdata->user_surname;
				$userpic = $userdata->image;
			}
			else
			{
				$username = '';	
    			$userpic = '';
			}	
         
			$arr[] = array(
					   'comment_id'=>$feed->comment_id,
					   'username'=>	$useraname,
					   'userpic'=>$userpic,
					   'comment'=>$feed->comment,
					   'comment_date'=>$feed->create_at
					);

    	}	

    	  $data['comment_data'] = $arr;
    } 	
	   $this->load->view('admin/blog/user_comment',$data);

}

public function  delete_user_comment($comment_id=false)
{  
	$delete = $this->common_model->deleteData('comment',array('comment_id'=>$comment_id,'type'=>'blog'));

	if($delete)
	{
		echo "1000"; exit;
	}
}

public function delete_status()
{
      $status = $this->input->post('status');
      $blog_id = $this->input->post('blog_id');
 
   	  $status_data = array('delete_status'=>$status);
   	  $status_update = $this->common_model->updateData('blog',$status_data,array('blog_id'=>$blog_id));

   	  if($status_update)
   	  {
   	  	 echo '1000'; exit;
   	  }	
}

public function like_user($blog_id = false)
{	
	$data = '';
	$user_arr = array();
	$data['blog_data'] = $this->common_model->getData('Like_Unlike',array('section_id'=>$blog_id,'type'=>'blog'),'like_unlike_id','DESC');

	if(!empty($data['blog_data']))
	{	
	   foreach($data['blog_data'] as $userinfo)
	   {
	   	    $user_id  = $userinfo->user_id;

   			$user_info = $this->common_model->common_getRow('users',array('user_id'=>$user_id)); 

   			$user_arr[] = $user_info;
	   }	

	   $data['user_data'] = $user_arr;

	}

    $this->load->view('admin/blog/liked_user',$data);

}


}





