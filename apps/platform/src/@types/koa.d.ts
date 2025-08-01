declare module 'koa' {
  export interface Context {
    request: any;
    response: any;
    state: any;
    app: any;
    req: any;
    res: any;
    query: any;
    body: any;
    [key: string]: any;
  }
  
  export interface Next {
    (): Promise<any>;
  }
  
  export interface ParameterizedContext<StateT = any, CustomT = any, BodyT = any> {
    request: any;
    response: any;
    state: StateT;
    app: any;
    req: any;
    res: any;
    query: any;
    body: BodyT;
    [key: string]: any;
  }
  
  export default class Koa {
    use(middleware: any): any;
    listen(port: number, callback?: () => void): any;
    callback(): any;
    [key: string]: any;
  }
}

declare module '@koa/router' {
  export interface ParamMiddleware {
    (param: string, ctx: any, next: any): any;
  }
  
  class Router<StateT = any, CustomT = any> {
    constructor(opts?: any);
    use(...middleware: any[]): Router<StateT, CustomT>;
    get(path: string, ...middleware: any[]): Router<StateT, CustomT>;
    post(path: string, ...middleware: any[]): Router<StateT, CustomT>;
    put(path: string, ...middleware: any[]): Router<StateT, CustomT>;
    patch(path: string, ...middleware: any[]): Router<StateT, CustomT>;
    delete(path: string, ...middleware: any[]): Router<StateT, CustomT>;
    param(param: string, middleware: any): Router<StateT, CustomT>;
    routes(): any;
    allowedMethods(): any;
    [key: string]: any;
  }
  
  export = Router;
}

declare module 'ajv' {
  export interface ErrorObject {
    [key: string]: any;
  }
  
  export interface JSONSchemaType<T> {
    [key: string]: any;
  }
  
  class Ajv {
    constructor(options?: any);
    compile(schema: any): any;
    validate(schema: any, data: any): boolean;
    getSchema<T>(schemaId: string): any;
    addSchema(schema: any, key?: string): any;
    [key: string]: any;
  }
  
  export = Ajv;
}

declare module 'ajv-formats' {
  function addFormats(ajv: any): void;
  export = addFormats;
}