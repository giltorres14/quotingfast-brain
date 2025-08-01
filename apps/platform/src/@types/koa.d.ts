declare module 'koa' {
  import { IncomingMessage, ServerResponse } from 'http';
  
  interface DefaultState {}
  interface DefaultContext {}
  
  export interface Context {
    request: any;
    response: any;
    state: any;
    app: Application;
    req: IncomingMessage;
    res: ServerResponse;
    [key: string]: any;
  }
  
  export interface Application {
    use(middleware: (ctx: Context, next: () => Promise<any>) => any): Application;
    listen(port: number, callback?: () => void): any;
    callback(): (req: IncomingMessage, res: ServerResponse) => void;
    [key: string]: any;
  }
  
  export default class Koa implements Application {
    use(middleware: (ctx: Context, next: () => Promise<any>) => any): Application;
    listen(port: number, callback?: () => void): any;
    callback(): (req: IncomingMessage, res: ServerResponse) => void;
    [key: string]: any;
  }
}