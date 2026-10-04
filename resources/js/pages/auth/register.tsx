import Login from './login';

interface PlanItem {
    id: number;
    nombre: string;
    precio: number | string;
    duracion_dias: number;
    tipo?: string;
}

interface RegisterPageProps {
    planes?: PlanItem[];
    canResetPassword?: boolean;
    status?: string;
}

export default function Register({ planes = [], canResetPassword = true, status }: RegisterPageProps) {
    return (
        <Login
            initialTab="register"
            planes={planes}
            canResetPassword={canResetPassword}
            status={status}
        />
    );
}
