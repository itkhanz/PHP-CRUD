# CRUD NOTES

## Namespaces & Autoloading with Composer

- To avoid name collision between class names we can use nsamespaces. It is generally a good idea to put all our sourcecode classes under the namespace `app`. so if you install a third part application that has a class Email, and your source code also has a class Email. The namespace `app` can be used for the source code classses so the class becomes `class app\Email`.

### Composer and Autoloading

- Download and install composer. open command prompt and type `composer` to see it is actually installed.
- Composer is a php dependency management tool, it allows us to find the third party packages and install using composer.
- To enable autoloading, run the `composer init` from the root folder of the project. It will generate a `composer.json`.
- To enable autoloading, add the foloowing lines in composer.json, where `psr-4` is the standard, `app\\` is the namespace, and `./` is the folder location for our classes.

```
 "autoload": {
    "psr-4": {
      "app\\": "./"
    }
  }
```

- Specify the `namespace app` in the classes files. so now for example the class Email can be instantiated with `$email = new app\Email();`
- we can alos specify `use app\Email` in our code, and instantiate the classes simply as `$email = new Email();`.

### Autoloading

- For a larger project with hundreds of classes, autoloading gives us the possiobility to avoid writing `require_once` for the classes, interfaces and traits.
- run the `composer update` command, this will create a vendor directory. Inside the vendor folder, it will create `autoload.php` so we only need to include this in our project and it will automatically load all the other classes. We just have to use `require_once "vendore/autoload.php";`

# PRODUCTS CRUD

- The root folder is the main project's folder i.e. `03_good`
- create a file index.php inside a `public` folder that contains all the web accessible files. We want `index.php` to handle all the requests and return the response.
- In the index.php, include the autoload.php so all the autoloading will be done by composer.
- Use the namespaces for the Router and controller in index.php:

```php
require_once __DIR__ . '/../vendor/autoload.php';

use app\Router;
use app\controllers\ProductController;
```

- We want to make the get or post requests to urls, and call the corresponding controllers methods inside a Router in index.php:

```php
$router = new Router();

$router->get('/', [ProductController::class, 'index']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/create', [ProductController::class, 'create']);
$router->post('/products/create', [ProductController::class, 'create']);
$router->get('/products/update', [ProductController::class, 'update']);
$router->post('/products/update', [ProductController::class, 'update']);
$router->post('/products/delete', [ProductController::class, 'delete']);


$router->resolve();

```

- Go to the `public` folder and start the sever `php -S localhost:8080`.
- In the browser, go to the `localhost:8080`, and it will return the output from index.php
- we want to create a custom router and configure that router for the multiple routes.

## MVC Controller

- Create a `controllers` folder inside the root folder of the project, and make a `ProductController.php` with the `namespace app\controllers`. Inside the ProductController, make a `public function index()` that will return a list of products. Similarly create seprate functions for create, update and delete.

```php
class productController
{
    public static function index() {
     echo "Index Page";
    }
    public static function update() {
     echo "update Page";
    }
    public static function create() {
     echo "create Page";
    }
    public static function delete() {
     echo "delete Page";
    }
}
```

- To enable autoloading, we have to make a composer.json, and for this purpose, type the `composer init` in the root folder.
- Configure the autoloading so any class inside a root folder will have a namespace app.

```
 "autoload": {
    "psr-4": {
      "app\\": "./"
    }
  }
```

- Run `composer update` that will make the autoload.php for us inside the vendor folder.
- create a `Router.php` class with namespace `app` inside root folder.
- `resolve()` will detect the current route and execute the corresponding function.
- Create functions for get and post that accept url and function, as well as resolve() function inside the Router class.
- We need to save the routes so we create an associate array $getRoutes and $postRoutes for url.
- To detect the current route information, var_dump the superglobal $\_SERVER inside resolve and see what information it gives.

```php
    public array $getRoutes = [];
    public array $postRoutes = [];

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function resolve()
    {
        echo '<pre>';
        echo $_SERVER;
        echo '</pre>';
    }

```

- the `PATH_INFO` key of the $\_SERVER will provide us with a URI, and the `REQUEST_METHOD` will give us the method.
- to get the function for currentUrl, we can check in the following way:
- The `fn` which needs to be executed is an array, where first index is the namespace, and the second index value is the function name.
  For example, localhost:8080/products/create will give us the $fn[0] as app\controllers\ProductController and $fn[1] as create.

```php
    public function resolve()
    {
        $currentUrl = $_SERVER['PATH_INFO'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }

        if ($fn) {
            //  echo '<pre>';
            // echo $fn;
            // echo '</pre>';
            call_user_func($fn);
        } else {
            echo 'Page not found';
        }
    }
```

### Database Connection

- Now we need to render a create or index page from the controller.
- Setup a Database class inside app namespace.
- As PDO is the global namespace but we are inside the app namespace, so to avoid the class error, `use PDO` will enable the use of PDO class.

```php
namespace app;

use PDO;

class Database
{
    public \PDO $pdo;   //$pdo variable is an instance of \PDO class
    public static ?Database $db = null;


    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;port=3306;dbname=products_crud', 'root', 'optiplex.520');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$db = $this;
    }

     public function getProducts($search = '')
    {
        // $search = $_GET['search'] ?? '';
        if ($search) {
            $statement = $this->pdo->prepare('SELECT * FROM products WHERE title LIKE :title ORDER BY create_date DESC');
            $statement->bindValue(':title', "%$search%");
        } else {
            $statement = $this->pdo->prepare('SELECT * FROM products ORDER BY create_date DESC');
        }


        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

- Now we need to call this `getProducts()` method from the productController, and also need to render the create form.

## MVC Views

- create a views->products folder, and make seprate view files for create, update, index and form that will be reused for create and update.
- we also need to create a `layout` that will contain header and footer.
- Write a simple h1 tag with a view description inside each of the view for testing purpose.
- Create a function `renderView` inside Router that will accept a view name as a relative path with parent folder.

```php
public function renderView($view)   // products/index
    {
        include_once __DIR__ . "/views/$view.php";
    }
```

- Now we want to use that renderView inside ProductController, for this purpose we need to somehow access Router inside controller.
- We can pass the Router as `call_user_func($fn, $this);` and accept it as an argument inside ProductController.

```php
use app\Router;
class ProductController
{
    public static function index(Router $router)
    {
        $router->renderView('products/index');
    }
}

```

- The outout will only have h1 tags of the view files. Create a `layout.php` that will contain our header and footer. Inside the body we can `<?php echo $content; ?>`.
- To save the value of view inside the content variable. The value of $content will be basically the HTML of view.

```php
public function renderView($view)   // products/index
    {
        ob_start(); //start caching of the output and saves it in a buffer
        include_once __DIR__ . "/views/$view.php";
        $content = ob_get_clean();  //returns the output and cleans the buffer
        include_once __DIR__ . "/views/layout.php";
    }
```

- In the productController, get all the products from the database and pass it down to the views.
- To access the instance of the Database inside ProductController, we first need to create it. Inside the Router class:

```php
class Router
{
    public Database $db;

    public function __construct()
    {
        $this->db = new Database(); //This database establishes connection to the mysql.
    }
}
```

- Now in the productsController, we can access the database as a property of the router class which has a method of getProducts `$products = $router->db->getProducts($search);`
- Pass this products as an associative array to the view. Add an additional paramerter to the renderView method of the router so we can pass our products arguments to the view, by default it is an empty array `public function renderView($view, $params = [])`
- The productController looks like as follows:

```php
class ProductController
{
    public static function index(Router $router)
    {
        $search = $_GET['search'] ?? '';
        $products = $router->db->getProducts($search);
        $router->renderView('products/index', [
            'products' => $products,
            'search' => $search
        ]);
    }
}
```

- Iterate over the paramas a key value pair inside the renderView method of the Router, and retrieve the products string in a variable:

```php
  foreach ($params as $key => $value) {
            $$key = $value;
        }
```

- So now the `products` array is available to us inside the renderView and we can use it in our index view file.
- Copy the app.css into the public folder.
- Move the images also in the public folder.
- Now create the controllers and views for the create, update, and delete.
- For the create product, change the url to indicate the create.php view inside index.php
  `<a href="/products/create" class="btn btn-success">Create Product</a>`
- As we need the same form both for the create as well as updtate, we copy the form code in a seprate file and include it in create.php view:

```php
<p>
    <a href="/products" class="btn btn-secondary">Back to products</a>
</p>
<h1>Create New Product</h1>

<?php include_once 'form.php' ?>
```

- In the form, we need the prodduct variable and errors variable. So in the productsController create function, we create a $product associative array with all the corresponding columns. And pass this productData and errors as parameters the renderView. Inside the form change the variable names to indicate the values of the product associative array i.e. $title to $product['title']

- Now we need to handle the submisison of form. For this we can create a product model. The product class will be a mapping of our class to database table.
- Inside the Product class, code the following:

```php
class Product
{
    public ?int $id = null; //? indicates the id is optional so we can assign null to it.
    public ?string $title = null;
    public ?string $description = null;
    public ?float $price = null;
    public ?string $imagePath = null;
    public ?array $imageFile = null;

    public function load($data)
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->price = (float)$data['price'];
        $this->imagePath = $data['image'] ?? null;
        $this->imageFile = $data['imageFile'] ?? null;
    }
}
```

- We need to load data from Product Controller

```php
public static function create(Router $router)
    {
        $errors = [];
        $productData = [
            'title' => '',
            'description' => '',
            'image' => '',
            'price' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productData['title'] = $_POST['title'];
            $productData['description'] = $_POST['description'];
            $productData['price'] = (float)$_POST['price'];
            $productData['imageFile'] = $_POST['image'] ?? null;

            $product = new Product();
            $product->load($productData);
            $errors = $product->save();
            if (empty($errors)) {
                header('Location: /products');
                exit;
            }
        }

        $router->renderView('products/create', [
            'product' => $productData,
            'errors' => $errors
        ]);
    }
```

- The save() method of the product will check for all the validations, and if there is no error then the product is successfully saved and we can redirect to the index page.
- We need to pass the product model to the Database class, and create a method inside the Database class to save the product into database.
- We check to see if this has id, and pass the product to either the create product or update product methods of the database.
- We need to get the database instance, and call the create or update method on it. One way is to access the database instance through router but we dont have router.
  -Altertively Singelton object exists on one instance of the class. As we dont have two instances of the database class. So we beed to access this singelton though the static property ` public static ?Database $db = null;`. And inside the constructor whenever we create a database, we can save the static property $db as a database instance `self::$db = $this;`.
- Now in the Product we can access the database as a static property `$db = Database::$db;`.

```php
$db = Database::$db;
            if ($this->id) {
                $db->updateProduct($this);
            } else {
                $db->createProduct($this);
            }
```

- We created updateProduct and createProduct methods inside the Database that accepts an instance of the product.
- The updateProduct controller just checks for the id in superglobal additionally:

```php
$id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /products');
            exit;
        }
```

- The detele controller is implemented in a simple manner as follows :

```php
public static function delete(Router $router)
    {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /products');
            exit;
        }

        $router->db->deleteProduct($id);
        header('Location: /products');
    }
```

- Also dont forget to edit the paths of updtae and delete links in the index view as follows:

```php
<a href="/products/update?id=<?php echo $product['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="post" action="/products/delete" style="display: inline-block">
```

-
