<?php
    namespace Drupal\module_manage_article\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Code\Database\Database;
    use Drupal\Core\Entity\ContentEntityForm;
    use Drupal\node\Entity\Node;
    use \Drupal\file\Entity\File;
    use Drupal\taxonomy\Entity\Term;

    class EditArticleForm extends FormBase {
        /**
         * {@inheritdoc}
         */
        
        public function getFormId(){
            return 'edit_article';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form,FormStateInterface $form_state ){
            $nid = \Drupal::routeMatch()->getParameter('id');
            $node = \Drupal\node\Entity\Node::load($nid);
            if($node->get('field_image')->target_id){
                $file = File::load($node->get('field_image')->target_id);
                // $image_name= $file->filename->value;
                $field_image =  $file->fid->value;
            }else{
                $field_image='';
            }

            $termId = $node->get('field_tags')->target_id;
        
            $term = Term::load($termId);
            if($term){
                $name_tag = $term->name->value;
            }else{
                $name_tag = '';
            }
           
            $form['title'] = array(
                '#type'=>'textfield',
                '#title'=>t('Title'),
                '#default_value'=> $node->title->value,
                '#required' => true,
            );
            $folder = date('Y-m', time());
            $form['field_image'] = [
                '#type' => 'managed_file',
                '#title' => t('Add a new file'),
                '#upload_validators' => [
                    'file_validate_extensions' => ['gif png jpg jpeg'],
                    'file_validate_size' => [25600000],
                ],
                '#upload_location' => 'public://'.$folder.'',
                '#default_value'=>[$field_image],
             ];
            $form['body_value'] = array(
                '#type'=>'text_format',
                '#title'=>'Body',
                '#default_value'=>$node->body->value,
                '#required' => true,
            );
            $form['field_tags'] = array(
                '#type'=>'textfield',
                '#title'=>'tags',
                '#default_value'=>$name_tag,
                '#description'=>'Enter a comma-separated list. For example: Amsterdam, Mexico City, "Cleveland, Ohio" ',
                '#required' => true,
            );
            $form['status'] = array(
                '#type'=>'checkbox',
                '#title' => t('Published'),
                '#default_value'=>$node->status->value,
            );
            $form['save'] = array(
                '#type'=>'submit',
                '#value'=>'update',
                '#button_type'=> 'primary'
            );
            return $form;

        }
        /**
         * {@inheritdoc}
         */
        public function validateForm(array &$form, FormStateInterface $form_state){
            $title =$form_state->getValue('title');
            if(trim($title) == ''){
                $form_state->setErrorByName('title',$this->t('Title field is required'));
            }
            elseif($form_state->getValue('body_value') ==''){
                $form_state->setErrorByName('body_value',$this->t('Body field is required'));
            }
        }
        /**
         * {@inheritdoc}
         */
        public function submitForm (array &$form, FormStateInterface $form_state ){
            $postData = $form_state->getValues();
            if($postData['field_image']){
                $fileId = $postData['field_image'][0]; 
            }else{
                $fileId=null;
            }
            $nid = \Drupal::routeMatch()->getParameter('id');
            $node = \Drupal\node\Entity\Node::load($nid);
            $termId = $node->get('field_tags')->target_id;
            $term = Term::load($termId);
            $term->name->setValue($postData['field_tags']);
            $term->Save();
             
            $node->title = $postData['title'];
            $node->body = $postData['body_value'];
            $node->field_image = $fileId;
            $node->save();


            $response  = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
            $response->send(); 
             
            \Drupal::messenger()->addStatus(t('Article data save successfully!'), 'status',TRUE);
        }

    }

