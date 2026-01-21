<!-- Modal Perfil -->
<div class="modal fade" id="perfilModal" tabindex="-1" aria-labelledby="perfilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">
            
            <div class="modal-header p-0 border-0">
                <div class="w-100 d-flex">
                    <div style="background:#FFD700; width:33%; height:8px;"></div>
                    <div style="background:#0033A0; width:33%; height:8px;"></div>
                    <div style="background:#CE1126; width:34%; height:8px;"></div>
                </div>
            </div>

            <div class="modal-body px-4 pb-4">
                <div class="text-center mb-4">
                    <h5 class="section-title px-3">Mi Cuenta</h5>
                    <h4 class="mb-0 fw-bold">ACTUALIZAR PERFIL</h4>
                </div>
                
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" 
                    data-bs-dismiss="modal" aria-label="Close">
                </button>

                <form id="formPerfilUpdate">
                    <input type="hidden" name="op" id="op_perfil" value="update" />
                    <input type="hidden" name="idVotantes" id="idVotantes_perfil" />

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre completo<span style="color: #2b4eb9;">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="nombre_completo_perfil" 
                                       name="nombre_completo" required placeholder="Nombre completo">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Correo electrónico<span style="color: #2b4eb9;">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="email_perfil" 
                                       name="email" required placeholder="Correo electrónico">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre de usuario<span style="color: #2b4eb9;">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="username_perfil" 
                                       name="username" required placeholder="Nombre de usuario">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contraseña actual<span style="color: #2b4eb9;">*</span></label>
                                <input type="password" class="form-control form-control-sm" id="current_password_perfil" 
                                       placeholder="Ingresa tu contraseña actual" required>
                                <small class="text-muted">Requerida para guardar cambios</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nueva contraseña</label>
                            <input type="password" class="form-control form-control-sm" id="password_perfil" 
                                name="password" placeholder="Contraseña nueva">
                            <small class="text-muted"></small> 
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control form-control-sm" id="password_confirm_perfil" 
                                       placeholder="Confirma la nueva contraseña">
                            </div>
                        </div>

                        <hr class="mt-2">

                    </div>

                    <div class="col-12 mt-3">
                        <div class="row g-3 justify-content-end">
                            <div class="col-auto">
                                <button type="button" class="btn btn-phoenix-secondary px-4 btn-sm" data-bs-dismiss="modal">
                                    Cancelar
                                </button>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-primary px-4 btn-sm" type="button" 
                                        onclick="PERFIL.validateAndUpdate();">
                                    Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>