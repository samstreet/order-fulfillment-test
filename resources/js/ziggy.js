const Ziggy = {"url":"http:\/\/localhost","port":null,"defaults":{},"routes":{"sanctum.csrf-cookie":{"uri":"sanctum\/csrf-cookie","methods":["GET","HEAD"]},"orders.index":{"uri":"api\/orders","methods":["GET","HEAD"]},"orders.store":{"uri":"api\/orders","methods":["POST"]},"orders.show":{"uri":"api\/orders\/{order}","methods":["GET","HEAD"],"parameters":["order"],"bindings":{"order":"id"}},"orders.destroy":{"uri":"api\/orders\/{order}","methods":["DELETE"],"parameters":["order"],"bindings":{"order":"id"}},"orders.update-status":{"uri":"api\/orders\/{order}\/status","methods":["PATCH"],"parameters":["order"],"bindings":{"order":"id"}},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]}}};
if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}
export { Ziggy };
