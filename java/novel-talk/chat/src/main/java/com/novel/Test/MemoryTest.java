package com.novel.Test;

import java.lang.management.ManagementFactory;
import java.lang.management.MemoryMXBean;
import java.lang.management.MemoryUsage;

public class MemoryTest {
        public static void main(String[] args) {
                MemoryMXBean memoryMXBean = ManagementFactory.getMemoryMXBean();
                MemoryUsage heapMemoryUsage = memoryMXBean.getHeapMemoryUsage();
                long initialHeapSize = heapMemoryUsage.getInit();
                
                System.out.println("Initial Heap Memory Size: " + initialHeapSize + " bytes");
        }
}
