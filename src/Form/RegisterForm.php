<?php 
    namespace Drupal\my_crud\Form;
    
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\Core\Database\Database;
    use Drupal\Core\Messenger;
    use  Drupal\Core\Entity\ContentEntityType;
 

    class RegisterForm extends FormBase 
    {
        public function getFormId(){
            return 'register_form';
        }

        public function buildForm(array $form, FormStateInterface $form_state)
        {   
            $conn = Database::getConnection();
            $record = [];

            if(isset($_GET['id']))
            {
                $query = $conn -> select('my_crud','m') -> condition('id', $_GET['id'])->fields('m');
                $record = $query->execute()->fetchAssoc();
            }

            $form['name'] =['#type'=>'textfield','#title' => t('Name:'),'#required'=>true,'#default_value'=> (isset($record['name']) ? $record['name'] : '')];

            $form['phone'] =['#type'=>'tel','#title' => t('Phone:'),'#required'=>true,'#default_value'=> (isset($record['phone']) ? $record['phone'] : '')];

            $form['email'] =['#type'=>'email','#title' => t('Email:'),'#required'=>true,'#default_value'=> (isset($record['email']) ? $record['email'] : '')];

            $form['action'] = ['#type'=>'action',];

            $form['actions']['submit'] = array(
                '#type' => 'submit',
                '#value' =>t('Save'),        
              );
              return $form;

        }

        public function validateForm(array &$form, FormStateInterface $form_state)
        {
            $name = $form_state -> getValue('name');
            if(!preg_match("/^[a-zA-Z-' ]*$/",$name))
            {
                $form_state->setErrorByName('name',$this->t('Name must be in characters only'));
            }
            $phone = $form_state->getValue('phone'); 
            if(preg_match('/^[0-9]{11}+$/', $phone)) {
                // the format /^[0-9]{11}+$/ will check for phone number with 10 digits and only numbers
                $form_state->setErrorByName('phone',$this->t('phone number with 11 digits and only numbers or invalid format'));
            }   
            

            $email = $form_state -> getValue('email');
            $temp_email = explode('@',$email);
            $string ="kyanon.digital";
            if($temp_email[1] != $string)
            {
                $form_state->setErrorByName('email',$this->t("Invalid email format. please use @kyanon.digital"));
            }
        }

        
        public function submitForm(array &$form, FormStateInterface $form_state)
        {
            $field = $form_state ->getValues();

            $name = $field['name'];
            $email = $field['email'];
            $phone = $field['phone'];

            if(isset($_GET['id']))
            {
                $field = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                );

                $query = \Drupal::database();
                $query->update('my_crud')->fields($field)->condition('id',$_GET['id'])->execute();
                $this -> messenger()->addMessage('Successfully updated record');
            }
            else
            {
                $field = array(
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                );

                $query = \Drupal::database();
                $query->insert('my_crud')->fields($field)->execute();
                $this -> messenger()->addMessage('Successfully save record');
            }
        }

    }

?>