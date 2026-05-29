pipeline {
    agent {
        label 'jenkins-agent'
    }

    // Khai báo tham số để người dùng nhập vào khi bấm Build
    parameters {
        string(name: 'IMAGE_VERSION', defaultValue: '', description: 'Nhập Version cho Image (VD: v1.0.0). Nếu để trống, hệ thống sẽ tự dùng số Build Number.')
    }

    environment {
        DOCKER_BUILDKIT = '1'
        IMAGE_NAME = 'thanhtruong123/orders' 
        // Nếu user nhập version thì dùng version đó, nếu không thì lấy BUILD_NUMBER
        IMAGE_TAG = "${params.IMAGE_VERSION ?: env.BUILD_NUMBER}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build') {
            steps {
                script {
                    echo "Building Docker image ${IMAGE_NAME}:${IMAGE_TAG}..."
                    sh "docker build -t ${IMAGE_NAME}:latest -t ${IMAGE_NAME}:${IMAGE_TAG} ."
                }
            }
        }

        stage('Deploy') {
            steps {
                script {
                    withCredentials([usernamePassword(credentialsId: 'dockerhub-creds', passwordVariable: 'DOCKER_PASSWORD', usernameVariable: 'DOCKER_USERNAME')]) {
                        echo "Logging into Docker Hub..."
                        sh 'echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin'
                        
                        echo "Pushing images to registry..."
                        sh "docker push ${IMAGE_NAME}:latest"
                        sh "docker push ${IMAGE_NAME}:${IMAGE_TAG}"
                    }
                }
            }
        }
    }

    post {
        always {
            script {
                echo "Cleaning up local Docker images..."
                sh "docker rmi ${IMAGE_NAME}:latest ${IMAGE_NAME}:${IMAGE_TAG} || true"
                sh "docker image prune -f --filter 'until=24h' || true"
            }
        }
    }
}