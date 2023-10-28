# Laravel Debug

Un Helper que permite hacer una depuración más simple y poderosa al unir en una sóla herramienta la depuración vía Base de datos, vía archivo Log o el tradicional DD. En caso de no estar disponible DD,  incorpora una herramienta alternativa de desarrollo propio.

Hay muchas situaciones en las cuales la salida vía DD no es factible, ya sea que la petición AJAX no funcione por error de programación o porque una recarga de la página corta la petición impidiendo la salida por consola o, en último caso, por un error 500 que interrumpe la ejecución del código PHP.

Independiente del caso, esta herramienta permite tener una salida de depuración a pesar de que la consola del navegador no está disponible. Sólo instancie la clase, defina los parametros según su preferencia y active el tipo de depuración deseada (por base de datos, por consola, por archivo log). Luego olvídese del navegadador.

** Si utilizará la depuracion por bd, defina el formato de  fecha que tenga definido.

Ejemplo con Log a tabla Debug_log:

  $debug=new Debug();
  
  $debug->stop=true;
    
  
  $debug->logbd_clean();
  
  $debug->set_title("nombres","filas","cantidad de filas");
    
  $debug->log_bd($names_url, $rows, $numrows);
