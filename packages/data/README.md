# Módulo de datos

Este módulo se encarga del acceso a la base de datos. Controla las entidades de datos y sus servicios.

La carpeta `src` contiene todas las clases que componen el módulo. Las entidades de datos son:

- [Ticket](src/Ticket.php)
- [Passenger](src/Passenger.php)
- Route
- Transport
- Service
- Location
- [Zone](src/Zone.php)

Puede ir a cada archivo por separado para ver su estructura y métodos. Notará que algunas entidades utilizan metadatos. Estos metadatos son campos de información flexible que pueden almacenar cualquier tipo de dato.

## Metadatos

La clase [`MetaManager`](src/MetaManager.php) nos permite manipular metadatos de acuerdo al tipo de entidad.

```php
# CÓMO GUARDAR UN METADATO
MetaManager::setMeta(
    MetaManager::TICKET,    # Tipo de metadato (ticket, transporte, ruta, pasajero, etc.)
    1,                      # ID del registro al que pertenece el metadato
    'clave',                # Clave del metadato
    'valor'                 # Valor del metadato
);

# CÓMO RECUPERAR UN METADATO
$meta = MetaManager::getMeta(
    MetaManager::TICKET,    # Tipo de metadato (ticket, transporte, ruta, pasajero, etc.)
    1,                      # ID del registro al que pertenece el metadato
    'clave',                # Clave del metadato
);

echo $meta; # El resultado sera 'valor';
```

## Archivo `functions.php`

El archivo [`functions.php`](functions.php) expone todas las funciones que tiene el módulo de datos. Se recomienda **nunca instanciar** objetos del módulo directamente, en su lugar, haga uso de las funciones que expone este archivo a manera de interfaz.

```php
# X ESTO NO SE RECOMIENDA
$ticket = new Ticket();

# ESTO SÍ RECOMIENDA
$ticket = git_ticket_create();

# Ambos obtienen el mismo resultado, pero se recomienda siempre usar el segundo método.
```

Los metodos de creación tienen algunas ventajas sobre la instanciación directa, por ejemplo, permiten parametros de inicialización.

```php
# Inicialización con instanciación
$ticket_by_intance = new Ticket();
$ticket_by_intance->id = 1;
$ticket_by_intance->total_amount = 1900;
$ticket_by_intance->setOrder(wc_get_order(2));

# Inicialización con método
$ticket_by_method = git_ticket_create([
    'id' => 1,
    'total_amount' => 1900,
    'id_order' => 2,
]);

# Ambos objetos son exactamente iguales en su contenido.
```

Supongamos que queremos crear un servicio y guardarlo en la base de datos, el proceso sería el siguiente:

```php
$service = git_service_create([
    'name' => 'Chaleco salvavidas',
    'icon' => 'https://services.com/chaleco.png',
    'price' => 0
]);

$service_saved = git_service_save($service);

echo $service_saved->id; // El ID del registro dentro de la base de datos.

# ------------------------------

# Todos los metodos de guardado permiten ya sea un objeto o el array asociativo. Por ejemplo, el mismo caso anterior también podría resolverse como:

# ------------------------------

$service_saved = git_service_save([
    'name' => 'Chaleco salvavidas',
    'icon' => 'https://services.com/chaleco.png',
    'price' => 0
]);

echo $service_saved->id; // El ID del registro dentro de la base de datos.
```

Se recomienda ver revisar todos los métodos del archivo `functions.php`, pero algunos de los más útiles pueden ser:

```php
function git_transport_check_availability(Transport|int $transport, Route|int $route, Date $date_trip, int $passengers_count = 1): bool|ErrorService;

function git_passenger_transfer(Passenger|int $passenger, Route|int $route, Transport|int $transport, Date $date_trip): bool|ErrorService;

function git_transport_set_maintenance(Transport|int $transport, Date $dateStart, Date $dateEnd): bool|ErrorService;

function git_ticket_toggle_flexible(Ticket|int $ticket, ?bool $force = null): bool|ErrorService;
```

Estos son ejemplos de algunos métodos que exponen el funcionamiento del módulo.

## Errores de servicio `ErrorService`

Algunos métodos pueden retornar un dato del tipo `ErrorService`. Este tipo de dato define los errores más comunes en transacciones de entre datos.

```php
enum ErrorService
{
    case NO_ERROR;
    case PASSENGER_NOT_APPROVED;
    case ROUTE_NOT_FOUND;
    case TICKET_NOT_FOUND;
    case PASSENGER_NOT_FOUND;
    case TRANSPORT_NOT_FOUND;
    case TRANSPORT_NOT_TAKE_ROUTE;
    case TRANSPORT_NOT_AVAILABLE;
    case INVALID_DATE_RANGE;
    case PASSENGERS_PENDING_TRIPS;
    case TICKET_NOT_FLEXIBLE;
    case TRANSPORT_DOES_NOT_TAKE_ROUTE;
}
```