<?php
    
    use Twig\Environment;
    use Twig\Error\LoaderError;
    use Twig\Error\RuntimeError;
    use Twig\Error\SyntaxError;
    use Twig\Extra\String\StringExtension;
    use Twig\Loader\FilesystemLoader;
    
    class View
    {
    
        private Environment $_twig;
        private FilesystemLoader $_loader;
        
        private array $_defaultParams = [];
        private array $_extraParams = [];
    
        private string $_controllerName = '';
    
        public string $title = '';
    
        public ?Controller $Controller = null;
    
        function __construct()
        {
            $this->_loader = new FilesystemLoader(VIEW_PATH);
            $this->_twig = new Environment($this->_loader);
            $this->_twig->addExtension(new StringExtension());
        }
        
        
        /**
         * Render the page
         * @param string $twigFileName
         * @param array $context
         * @param bool $printout
         * @return void
         */
        public function render(string $twigFileName, array $context = [], bool $printout = false): void
        {
            $twigFileName = $twigFileName . '.twig';
            if(!$this->_loader->exists($twigFileName))
                die("Template not found [$twigFileName]");
    
            if(!$context)
                $context = [];
            
            $this->setDefaultParams();
            
            $params = array_merge($context, $this->_defaultParams, $this->_extraParams);
    
            try
            {
                echo $this->_twig->render($twigFileName, $params);
            }
            catch (LoaderError $e)
            {
                error_log($e->getMessage());
                echo 'Error loading template engine';
            }
            catch (RuntimeError $e)
            {
                error_log($e->getMessage());
                echo 'Runtime Error in template engine';
            }
            catch (SyntaxError $e)
            {
                error_log($e->getMessage());
                echo 'Syntax Error in template engine';
            }
            
            
            if($printout && Config::LOCALTEST)
            {
                array_walk_recursive($params, function(&$v) {
                    if($v != null && is_string($v))
                        $v = htmlspecialchars($v);
                });
    
                echo '<hr><pre>';
                if(isset($_POST))
                {
                    echo "POST:\n";
                    print_r($_POST);
                }
                
                print_r($params);
                echo '</pre>';
            }
    
        }
        
        /**
         * Initialize the default parameters that are sent to every template page
         * @return void
         */
        private function setDefaultParams(): void
        {
            $this->_defaultParams = [
                'title' => (strlen($this->title) > 0 ? $this->title : DEFAULT_TITLE),
                'path' => URL,
                'controller' => $this->_controllerName,
                'breadcrumbs' => Breadcrumbs::getBreadcrumbs(),
                'user' => User::Instance()
            ];
        }

        
        /**
         * Shortcut to render the error page with a message
         * @param string $message
         * @return void
         */
        public function renderError(string $message): void
        {
            $this->render('error/message', [
                'message' => $message
            ]);
        }
        
        
        /**
         * Shortcut to render the error page but with a success message
         * @param string $message
         * @return void
         */
        public function renderSuccess(string $message): void
        {
            $this->render('error/success', [
                'message' => $message
            ]);
        }
    
    }

