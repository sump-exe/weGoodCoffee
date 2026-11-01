<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Admin | WeGoodCoffee</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" aria-label="WeGoodCoffee home"><span class="brand-name">WeGood<strong>Coffee</strong></span>
        </a>
    </header>

    <main class="admin-stage container-fluid">
        <div class="row justify-content-center">
            <section class="col-12 col-xl-10 admin-content" aria-labelledby="users-title">
                <div class="admin-heading row align-items-center g-3">
                    <div class="col">
                        <h1 id="users-title">Users</h1>
                    </div>
                    <div class="col-auto">
                        <button class="btn admin-add-button" type="button">Add User</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="admin-table-wrap">
                            <table class="table admin-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Role</th>
                                        <th scope="col" class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>admin</td>
                                        <td><span class="role-badge">Admin</span></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm admin-edit-button" type="button">Edit</button>
                                                <button class="btn btn-sm admin-delete-button" type="button">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>barista</td>
                                        <td><span class="role-badge">Staff</span></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn btn-sm admin-edit-button" type="button">Edit</button>
                                                <button class="btn btn-sm admin-delete-button" type="button">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
</body>
</html>
