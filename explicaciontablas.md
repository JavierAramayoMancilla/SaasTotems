Tabla Tenants - Empresas/CLientes
para esta tabla para la vista del superadministrador seria bueno tener un show o get de todos y por identificadores como por ejemplo segun el nombre,tambien el tema de las slugs que es el link que deberia de ser unico por cliente/empresa ,entonces me parece bien un metodo crud con los get que mencione, post,put y delete pero que no lo elimine totalmente, simplemente una vez que termine su suscripcion que se cambie su estado en desactivado , en caso de que incumpla algunas normas del servicio que se cambien a suspendido, en todo caso el controlador para el delete deberia de buscar por el nombre aunq igual seria colocar como un atributo unico como un codigo que seria su identificador unico, asi en el frontend simplemente seria un boton con un menu despegable para que me de la opcion de suspender cuenta, la parte de activa o inactiva basicamente se haria solo depende de la suscripcion.
sobre el correo deberia ser unico y la contrasena hasheada, el usuario solo deberia tener opcion de modificar sus datos como nombre correo,etc
tambien hay que agregar un atributo codigo que se le utilizara como identidicardor unoco de cada cliente , ya que aveces puede pasar aunqe no deberia que ambas empresas tenga el mismo nombre asi que debemos agregar eso .

Tabla Users - Usuarios
Esto basicamente maneja todos los usuarios tanto de los servicios de un saas o de el super usuario, como este maneja el atributo tenant_id que es la relacion con cada proyecto pue solamanete para el administrador dueno del saas que tiene que tener un dashorad como panel administratio aparte este atributo deberia de ser null asi que solo para el deberia de ser null pero los demas deberia de ser creados solamente cuando se obtenga un servicioy estos si tienen que tener el tenant id ya que sea como su relacion para saber a que empresa/cliente pertenecen , tambien deberiam de tener un atributo de codigo para que cuando este se lo deshablite este como inactivo y ono como eliminado directamente.

Tabla Totems - Pantalla o Publicidad
esta tabla de los display donde le cliente crea y administra sus puntos de reproduccion , esta tabla esta para que los usuario asocien las publicidades creadas a un display, asi que esto es de gran utilidad para dividir publicidades segun las zonas, como cafeteria, entrada y eso,sobre los crud solo quiero los 4 estarian genial,get,get por id, post,put y delete

Advertisements - Publicidades/Anuncios

Sobre esta tabla la unica observacion que tengo es que el tema de la duracion deberia de ser limitada con un minimo de 5 y un maximo de 15 segundos , asi nadie ocuparia el carrusel de publicidades 
sobre lo controalodares seria un get para mistrar las publicidades creadas, un update y post para crear y actulizar y delete igualmente para deshabilitarlo no borrarlo, tambien seria bueno un buscador logico mediante el nombre de la publicidad

Display_advertisements - DisplayPublicidades

esta tabla va cambiar,entonces ya no sera totems advertisements sino displayadvertisements , a lo que entendi esta tabla es basicamente el display que contendra todos lo anuncions publicitarios que crearan los clientes sobre sus empresas, nose como hacerlo sobre los atributos on tengo ninguna observacion, entonces sobre los crud solo seria un get para ver los que crearon ya que estos seran una composion de publicidades, despues un post que basicamente seria para anadir las publicidades y la posicin supongo que mas alla lo vere un update para actualizar y delete para borrar el display pero de manera logica y como el otro que se borre pero despues de una semana sin uso como tiene el atruibuto is_active pue usaria ese

Tabla ad_Schedules 
 esta tabla maneja lo que es el tiempo que se maneja al momento de mostrar asi que sobre los atributos creo que esta bien ya que esta todo controlador de una manera uniforme, para los crud seria bueno los 4 basicos

Tabla Menus
esto basicamente ya es la publicidad que el cliente crea sobre los atributos no tengo observaciones ahora para los crud, diria que todos estan bien , get,post put y delete

Tabla Menu-items
sobre los atributos lo veo todo bien y los crud nose bien como se manejara esto porque estas seran como las piezas que le dare al usuario para que arme su publicidad, ahi necesito recomendaciones

-tabla ad_impressions Reproducciones de Publicidad 